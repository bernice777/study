#!/usr/bin/env bash
set -euo pipefail

binary="${1:-./argument}"

if [[ ! -x "$binary" ]]; then
    echo "[FAIL] 실행 파일을 찾을 수 없습니다: $binary" >&2
    exit 1
fi

machine="$(readelf -h "$binary" | awk -F: '/Machine:/ {gsub(/^[[:space:]]+/, "", $2); print $2}')"
if [[ "$machine" != "Advanced Micro Devices X86-64" ]]; then
    echo "[FAIL] x86-64 바이너리가 아닙니다: $machine" >&2
    exit 1
fi

program_output="$($binary)"
for expected in \
    "first   = 1" \
    "second  = 2" \
    "third   = 3" \
    "fourth  = 4" \
    "fifth   = 5" \
    "sixth   = 6" \
    "seventh = 7"; do
    if ! grep -Fqx "$expected" <<<"$program_output"; then
        echo "[FAIL] 프로그램 출력에서 '$expected'를 찾지 못했습니다." >&2
        exit 1
    fi
done

main_disassembly="$(objdump -d -M intel "$binary" | sed -n '/<main>:/,/^$/p')"
for instruction in \
    "endbr64" \
    "push   rbp" \
    "mov    rbp,rsp" \
    "sub    rsp,0x8" \
    "push   0x7" \
    "mov    r9d,0x6" \
    "mov    r8d,0x5" \
    "mov    ecx,0x4" \
    "mov    edx,0x3" \
    "mov    esi,0x2" \
    "mov    edi,0x1" \
    "call" \
    "add    rsp,0x10" \
    "leave" \
    "ret"; do
    if ! grep -Fq "$instruction" <<<"$main_disassembly"; then
        echo "[FAIL] main에서 '$instruction'을 찾지 못했습니다." >&2
        exit 1
    fi
done

main_address_hex="$(nm -n "$binary" | awk '$3 == "main" {print $1}')"
push_address_hex="$(objdump -d -M intel "$binary" | awk '
    /<main>:/ {in_main=1; next}
    in_main && /push[[:space:]]+0x7/ {gsub(":", "", $1); print $1; exit}
    in_main && NF == 0 {exit}
')"

main_address=$((16#$main_address_hex))
push_address=$((16#$push_address_hex))
push_offset=$((push_address - main_address))
if (( push_offset != 12 )); then
    echo "[FAIL] push 0x7의 오프셋은 main+$push_offset 입니다. 기대값은 main+12입니다." >&2
    exit 1
fi

echo "[PASS] x86-64 호출 규약 실습 바이너리 검증 완료 (push 0x7 = main+12)"
