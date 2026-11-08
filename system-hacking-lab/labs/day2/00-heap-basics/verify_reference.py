#!/usr/bin/env python3
from pathlib import Path
from signal import SIGABRT
from subprocess import PIPE, run

HERE = Path(__file__).resolve().parent


def execute(name: str, check: bool = True):
    return run(
        [str(HERE / name)],
        cwd=HERE,
        stdout=PIPE,
        stderr=PIPE,
        timeout=3,
        check=check,
    )


layout = execute("heap_layout").stdout
assert b"sizeof(Node)=16" in layout and b"next_offset=8" in layout, layout

residual = execute("uninitialized").stdout
assert b"reused=1" in residual and b"residual=SECRET" in residual, residual

confusion = execute("type_confusion").stdout
assert confusion == b"SECRET\n", confusion

double_free = execute("double_free", check=False)
assert double_free.returncode == -SIGABRT, double_free.returncode
assert b"double free" in double_free.stderr.lower(), double_free.stderr

print("[PASS] Heap layout / Uninitialized / Double Free / Type Confusion 검증 완료")
