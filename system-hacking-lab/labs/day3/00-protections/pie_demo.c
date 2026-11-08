#include <stdio.h>

int counter = 7;

int main(void)
{
    printf("main=%p\n", (void *)main);
    printf("counter=%p\n", (void *)&counter);
    return 0;
}
