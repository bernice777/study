#include <stdio.h>

__attribute__((noinline))
int add(int a, int b)
{
    int result = a + b;
    return result;
}

int main(void)
{
    int answer = add(4, 5);
    printf("%d\n", answer);
    return 0;
}
