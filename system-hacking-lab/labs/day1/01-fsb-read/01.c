#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>

char flag_buf[64];

static void load_flag(void)
{
    FILE *fp = fopen("flag.txt", "r");
    if (fp == NULL) {
        perror("flag.txt");
        exit(1);
    }
    (void)fread(flag_buf, 1, sizeof(flag_buf) - 1, fp);
    fclose(fp);
}

int main(void)
{
    char buf[256];

    setvbuf(stdin, NULL, _IONBF, 0);
    setvbuf(stdout, NULL, _IONBF, 0);
    memset(buf, 0, sizeof(buf));
    load_flag();

    printf("Input: ");
    (void)read(STDIN_FILENO, buf, sizeof(buf) - 1);
    printf(buf); /* Intentionally vulnerable for the local course lab. */
    return 0;
}
