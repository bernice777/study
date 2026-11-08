#include <stdlib.h>
#include <stdio.h>
#include <unistd.h>

__attribute__((noinline))
void vuln(void)
{
    (void)system("/bin/sh");
}

__attribute__((noinline))
static void read_input(void)
{
    char buf[512];

    puts("Send payload:");
    (void)read(STDIN_FILENO, buf, 520);
}

int main(void)
{
    /* Keep a real caller stack frame so its epilogue uses leave; ret. */
    volatile char frame_anchor[16] = {0};

    setvbuf(stdout, NULL, _IONBF, 0);
    (void)frame_anchor;
    read_input();
    puts("returned through original RIP");
    return 0;
}
