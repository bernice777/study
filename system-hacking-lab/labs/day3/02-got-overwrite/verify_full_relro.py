#!/usr/bin/env python3
from pathlib import Path
from signal import SIGSEGV

from pwn import ELF, context, process

HERE = Path(__file__).resolve().parent
BINARY = HERE / "got_overwrite_full"
EXPECTED = b"KSJ{GOT_OVERWRITE_VERIFIED}"

context.binary = elf = ELF(str(BINARY), checksec=False)
context.log_level = "error"
assert elf.relro == "Full", elf.relro

tube = process([str(BINARY)], cwd=str(HERE))
tube.sendlineafter(b"command:\n", b"cat flag.txt")
tube.sendlineafter(b"where (hex): ", hex(elf.got["puts"]).encode())
tube.sendlineafter(b"what  (hex): ", hex(elf.plt["system"]).encode())
output = tube.recvall(timeout=2)
status = tube.poll(block=True)
assert EXPECTED not in output, output
assert status == -SIGSEGV, status
print("[PASS] Full RELRO: puts@GOT 쓰기 SIGSEGV 차단 확인")
