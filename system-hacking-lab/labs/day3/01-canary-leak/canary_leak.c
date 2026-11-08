#include <stdio.h>
#include <unistd.h>

__attribute__((naked, used))
void ret_only(void)
{
    __asm__("ret");
}

__attribute__((noinline, used))
void win(void)
{
    puts("KSJ{CANARY_LEAK_BYPASS_VERIFIED}");
    _exit(0);
}

__attribute__((noinline))
static void vuln(void)
{
    char buf[0x100];

    puts("stage1: overwrite only the canary NUL byte");
    (void)read(STDIN_FILENO, buf, 0x109);
    puts("echo:");
    puts(buf);

    puts("stage2: preserve the leaked canary");
    (void)read(STDIN_FILENO, buf, 0x200);
}

int main(void)
{
    setvbuf(stdin, NULL, _IONBF, 0);
    setvbuf(stdout, NULL, _IONBF, 0);
    vuln();
    return 0;
}
