#include <errno.h>
#include <stdint.h>
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>

typedef struct {
    char *ptr;
} String;

typedef struct {
    uint64_t number;
} Number;

String *string_slot;
Number *number_slot;
uint64_t flag;

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

static void read_exact(void *buf, size_t size)
{
    size_t done = 0;
    while (done < size) {
        ssize_t count = read(STDIN_FILENO, (char *)buf + done, size - done);
        if (count <= 0) {
            exit(0);
        }
        done += (size_t)count;
    }
}

static void create_string(void)
{
    if (string_slot != NULL) {
        puts("string slot already used");
        return;
    }
    string_slot = malloc(sizeof(String));
    if (string_slot == NULL) {
        exit(1);
    }
    string_slot->ptr = calloc(1, 0x20);
    if (string_slot->ptr == NULL) {
        exit(1);
    }
    puts("string created");
}

static void set_string(void)
{
    if (string_slot == NULL) {
        puts("no string");
        return;
    }
    printf("size: ");
    uint64_t size = read_number();
    if (size == 0 || size > 0x20) {
        puts("invalid size");
        return;
    }
    printf("data: ");
    /* UAF: string_slot may point to a chunk reused as Number. */
    read_exact(string_slot->ptr, (size_t)size);
    puts("string updated");
}

static void delete_string(void)
{
    if (string_slot == NULL) {
        return;
    }
    free(string_slot->ptr);
    free(string_slot);
    /* Vulnerability: string_slot is intentionally not set to NULL. */
    puts("string deleted");
}

static void create_number(void)
{
    if (number_slot != NULL) {
        puts("number already exists");
        return;
    }
    number_slot = malloc(sizeof(Number));
    if (number_slot == NULL) {
        exit(1);
    }
    printf("number: ");
    number_slot->number = read_number();
    puts("number created");
}

static void check_flag(void)
{
    if (flag == 0x31337) {
        puts("KSJ{USE_AFTER_FREE_AAW_VERIFIED}");
    } else {
        printf("flag = %#lx\n", (unsigned long)flag);
    }
}

static void menu(void)
{
    puts("1. create string");
    puts("2. set string");
    puts("3. delete string");
    puts("4. create number");
    puts("5. check");
    puts("6. exit");
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
            create_string();
            break;
        case 2:
            set_string();
            break;
        case 3:
            delete_string();
            break;
        case 4:
            create_number();
            break;
        case 5:
            check_flag();
            break;
        case 6:
            return 0;
        default:
            puts("invalid menu");
            break;
        }
    }
}
