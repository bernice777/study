#!/usr/bin/env python3
from pathlib import Path
from re import findall
from subprocess import PIPE, run

from pwn import ELF, context

HERE = Path(__file__).resolve().parent
context.log_level = "error"


def output(name: str) -> bytes:
    return run(
        [str(HERE / name)],
        cwd=HERE,
        stdout=PIPE,
        stderr=PIPE,
        timeout=3,
        check=True,
    ).stdout


def addresses(name: str):
    return tuple(int(value, 16) for value in findall(rb"0x([0-9a-f]+)", output(name)))


aslr_runs = [addresses("aslr_demo") for _ in range(3)]
assert all(len(values) == 3 for values in aslr_runs), aslr_runs
for column in range(3):
    assert len({values[column] for values in aslr_runs}) == 3, aslr_runs

pie_off_runs = [addresses("pie_off") for _ in range(3)]
assert len(set(pie_off_runs)) == 1, pie_off_runs

pie_on_runs = [addresses("pie_on") for _ in range(3)]
assert len(set(pie_on_runs)) == 3, pie_on_runs
assert len({counter - main for main, counter in pie_on_runs}) == 1, pie_on_runs

off = ELF(str(HERE / "pie_off"), checksec=False)
on = ELF(str(HERE / "pie_on"), checksec=False)
lazy = ELF(str(HERE / "lazy_binding"), checksec=False)
assert off.pie is False and on.pie is True
assert lazy.relro == "Partial" and "puts" in lazy.got and "puts" in lazy.plt
assert output("lazy_binding") == b"first call\nsecond call\n"

print("[PASS] ASLR / PIE OFF·ON / PLT·GOT Lazy Binding 검증 완료")
