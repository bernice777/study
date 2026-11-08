#include <stdio.h>

/* noinline keeps the call visible even if students later change build flags. */
__attribute__((noinline))
void func1(int first, int second, int third, int fourth,
           int fifth, int sixth, int seventh)
{
    printf("first   = %d\n", first);
    printf("second  = %d\n", second);
    printf("third   = %d\n", third);
    printf("fourth  = %d\n", fourth);
    printf("fifth   = %d\n", fifth);
    printf("sixth   = %d\n", sixth);
    printf("seventh = %d\n", seventh);
}

int main(void)
{
    func1(1, 2, 3, 4, 5, 6, 7);
    return 0;
}
