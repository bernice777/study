#include <errno.h>
#include <stdint.h>
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>

#define SLOT_COUNT 8
#define MAX_IO 1024

typedef struct {
    uint8_t *ptr;
    uint64_t size;
} Data;

Data *allocates[SLOT_COUNT];
uint64_t flag;

static ssize_t read_exact(void *buf, size_t size)
{
    size_t done = 0;
    while (done < size) {
        ssize_t count = read(STDIN_FILENO, (char *)buf + done, size - done);
        if (count <= 0) {
            return count;
        }
        done += (size_t)count;
    }
    return (ssize_t)done;
}

static uint64_t read_number(void)
{
    char line[64] = {0};
    if (fgets(line, sizeof(line), stdin) == NULL) {
        exit(0);
    }
    errno = 0;
    unsigned long long value = strtoull(line, NULL, 0);
    if (errno != 0) {
        exit(1);
    }
    return (uint64_t)value;
}

static void create_data(void)
{
    for (size_t i = 0; i < SLOT_COUNT; ++i) {
        if (allocates[i] == NULL) {
            allocates[i] = calloc(1, sizeof(Data));
            if (allocates[i] == NULL) {
                exit(1);
            }
            printf("created index %zu\n", i);
            return;
        }
    }
    puts("full");
}

static Data *choose_data(void)
{
    printf("index: ");
    uint64_t index = read_number();
    if (index >= SLOT_COUNT || allocates[index] == NULL) {
        puts("invalid index");
        return NULL;
    }
    return allocates[index];
}

static void insert_data(void)
{
    Data *data = choose_data();
    if (data == NULL || data->ptr != NULL) {
        return;
    }

    printf("size: ");
    uint64_t size = read_number();
    if (size == 0 || size > 16) {
        puts("invalid size");
        return;
    }

    data->ptr = malloc((size_t)size);
    if (data->ptr == NULL) {
        exit(1);
    }
    data->size = size;
    printf("data: ");
    if (read_exact(data->ptr, (size_t)size) <= 0) {
        exit(0);
    }
    puts("inserted");
}

static void modify_data(void)
{
    Data *data = choose_data();
    if (data == NULL || data->ptr == NULL) {
        return;
    }

    printf("size: ");
    uint64_t size = read_number();
    if (size == 0 || size > MAX_IO) {
        puts("invalid size");
        return;
    }

    printf("data: ");
    /* Vulnerability: size is not checked against data->size. */
    if (read_exact(data->ptr, (size_t)size) <= 0) {
        exit(0);
    }
    puts("modified");
}

static void check_flag(void)
{
    if (flag == 0x31337) {
        puts("KSJ{HEAP_OVERFLOW_AAW_VERIFIED}");
    } else {
        printf("flag = %#lx\n", (unsigned long)flag);
    }
}

static void menu(void)
{
    puts("1. create");
    puts("2. insert");
    puts("3. modify");
    puts("4. check");
    puts("5. exit");
    printf("> ");
}

int main(void)
{
    setvbuf(stdin, NULL, _IONBF, 0);
    setvbuf(stdout, NULL, _IONBF, 0);

    for (;;) {
        menu();
        switch (read_number()) {
        case 1:
            create_data();
            break;
        case 2:
            insert_data();
            break;
        case 3:
            modify_data();
            break;
        case 4:
            check_flag();
            break;
        case 5:
            return 0;
        default:
            puts("invalid menu");
            break;
        }
    }
}
