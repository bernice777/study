#include <stdint.h>
#include <stdio.h>

union Value {
    uint64_t number;
    char *text;
};

int main(void)
{
    union Value value;
    value.number = (uintptr_t)"SECRET";
    puts(value.text);
    return 0;
}
