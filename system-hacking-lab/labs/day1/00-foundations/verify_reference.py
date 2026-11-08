#!/usr/bin/env python3
from pathlib import Path
from subprocess import PIPE, run

HERE = Path(__file__).resolve().parent


def execute(name: str, data: bytes = b"") -> bytes:
    result = run(
        [str(HERE / name)],
        cwd=HERE,
        input=data,
        stdout=PIPE,
        stderr=PIPE,
        timeout=3,
        check=True,
    )
    return result.stdout


assert execute("gdb_call") == b"9\n"
assert b"total=0x20\nACCEPT" in execute("integer_overflow")
assert b"value=7331" in execute("oob_read", b"-1\n")
assert b"guard=A" in execute("off_by_one")
print("[PASS] GDB call / Integer Overflow / OOB / Off-by-One 검증 완료")
