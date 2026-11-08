#include <stdint.h>
#include <stdio.h>
#include <string.h>
#include <unistd.h>

/* The linker fixes this educational target at 0x0804c028. */
extern int changeMe;

struct InputFrame {
    uint32_t stack_pad[3];
    char buf[80];
    uint32_t stack_tail;
};

int main(void)
{
    struct InputFrame input;

    setvbuf(stdin, NULL, _IONBF, 0);
    setvbuf(stdout, NULL, _IONBF, 0);
    memset(&input, 0, sizeof(input));

    printf("Input: ");
    (void)read(STDIN_FILENO, input.buf, sizeof(input.buf) - 1);
    printf(input.buf); /* Input begins at printf's 7th stack argument. */
    printf("\nchangeMe=%d\n", changeMe);
    if (changeMe == 327) {
        puts("KSJ{FSB_PERCENT_N_WRITE_VERIFIED}");
    }
    return 0;
}
