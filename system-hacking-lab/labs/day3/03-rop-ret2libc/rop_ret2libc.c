#include <stddef.h>
#include <unistd.h>

__attribute__((naked, used))
void pop_rdi_rsi_rdx_ret(void)
{
    __asm__("pop %rdi; pop %rsi; pop %rdx; ret");
}

__attribute__((noinline))
static void vuln(void)
{
    char buf[0x40];
    static const char prompt[] = "payload:\n";

    (void)write(STDOUT_FILENO, prompt, sizeof(prompt) - 1);
    (void)read(STDIN_FILENO, buf, 0x300);
}

int main(void)
{
    vuln();
    return 0;
}
