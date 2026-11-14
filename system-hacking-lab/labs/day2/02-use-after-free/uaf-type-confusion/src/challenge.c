#include <errno.h>
#include <inttypes.h>
#include <stddef.h>
#include <stdint.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#define AUTH_MAGIC UINT64_C(0x41555448454e5449)
#define ADMIN_MAGIC UINT64_C(0x41444d494e5f4f4b)

typedef struct {
    char username[32];
    uint64_t authenticated;
    uint64_t access_level;
} Session;

typedef struct {
    char title[32];
    uint64_t category;
    uint64_t value;
} Memo;

_Static_assert(sizeof(Session) == sizeof(Memo), "objects must have equal size");
_Static_assert(offsetof(Session, authenticated) == offsetof(Memo, category),
               "authentication/category offsets must overlap");
_Static_assert(offsetof(Session, access_level) == offsetof(Memo, value),
               "access/value offsets must overlap");

Session *g_session;
Memo *g_memo;
int g_session_created;
int g_session_freed;

__attribute__((noinline)) void debug_after_a_alloc(void) { __asm__ volatile(""); }
__attribute__((noinline)) void debug_after_a_free(void) { __asm__ volatile(""); }
__attribute__((noinline)) void debug_after_b_alloc(void) { __asm__ volatile(""); }
__attribute__((noinline)) void debug_after_b_input(void) { __asm__ volatile(""); }
__attribute__((noinline)) void debug_before_check(void) { __asm__ volatile(""); }
__attribute__((noinline)) void debug_before_flag(void) { __asm__ volatile(""); }

static int read_line(char *buffer, size_t size) {
    size_t length;
    int ch;

    if (fgets(buffer, (int)size, stdin) == NULL) {
        return 0;
    }
    length = strlen(buffer);
    if (length > 0 && buffer[length - 1] == '\n') {
        buffer[length - 1] = '\0';
    } else {
        while ((ch = getchar()) != '\n' && ch != EOF) {
        }
    }
    return 1;
}

static int read_u64(const char *prompt, uint64_t *result) {
    char buffer[64];
    char *end;
    unsigned long long value;

    printf("%s", prompt);
    if (!read_line(buffer, sizeof(buffer))) {
        return 0;
    }
    errno = 0;
    end = NULL;
    value = strtoull(buffer, &end, 0);
    if (errno != 0 || end == buffer || *end != '\0') {
        puts("Invalid number.");
        return 0;
    }
    *result = (uint64_t)value;
    return 1;
}

static void print_flag(void) {
    const char *path = getenv("FLAG_PATH");
    char buffer[256];
    FILE *file;

    if (path == NULL || *path == '\0') {
        path = "./flag";
    }
    file = fopen(path, "r");
    if (file == NULL) {
        puts("Flag file is unavailable.");
        return;
    }
    if (fgets(buffer, sizeof(buffer), file) != NULL) {
        printf("FLAG: %s", buffer);
        if (strchr(buffer, '\n') == NULL) {
            putchar('\n');
        }
    }
    fclose(file);
}

static void create_session(void) {
    if (g_session_created) {
        puts("Session already created.");
        return;
    }
    g_session = calloc(1, sizeof(*g_session));
    if (g_session == NULL) {
        puts("Allocation failed.");
        exit(EXIT_FAILURE);
    }
    memcpy(g_session->username, "guest", sizeof("guest"));
    g_session_created = 1;
    puts("Session A created.");
    debug_after_a_alloc();
}

static void show_session(void) {
    if (!g_session_created || g_session_freed) {
        puts("No live session to display.");
        return;
    }
    printf("User: %.32s\nAuthenticated: 0x%016" PRIx64
           "\nAccess level: 0x%016" PRIx64 "\n",
           g_session->username, g_session->authenticated, g_session->access_level);
}

static void destroy_session(void) {
    if (!g_session_created) {
        puts("Create Session A first.");
        return;
    }
    if (g_session_freed) {
        puts("Session A was already freed; second free blocked.");
        return;
    }
    free(g_session);
    g_session_freed = 1;
    /* Intentionally vulnerable: g_session is not set to NULL. */
    puts("Session A freed.");
    debug_after_a_free();
}

static void create_memo(void) {
    if (g_memo != NULL) {
        puts("Memo B already created.");
        return;
    }
    g_memo = malloc(sizeof(*g_memo));
    if (g_memo == NULL) {
        puts("Allocation failed.");
        exit(EXIT_FAILURE);
    }
    memset(g_memo, 0, sizeof(*g_memo));
    debug_after_b_alloc();
    printf("Title (max 31 bytes): ");
    if (!read_line(g_memo->title, sizeof(g_memo->title))) {
        exit(EXIT_SUCCESS);
    }
    if (!read_u64("Category: ", &g_memo->category) ||
        !read_u64("Value: ", &g_memo->value)) {
        memset(g_memo, 0, sizeof(*g_memo));
        puts("Memo creation cancelled.");
        return;
    }
    puts("Memo B created.");
    debug_after_b_input();
}

static void check_access(void) {
    if (!g_session_created || !g_session_freed) {
        puts("A freed Session is required.");
        return;
    }
    debug_before_check();
    if (g_session->authenticated == AUTH_MAGIC &&
        g_session->access_level == ADMIN_MAGIC) {
        puts("Administrator access granted.");
        debug_before_flag();
        print_flag();
    } else {
        puts("Access denied.");
    }
}

static void menu(void) {
    puts("\n1. Create Session A");
    puts("2. Show Session A");
    puts("3. Free Session A");
    puts("4. Create Memo B");
    puts("5. Check Session A privileges");
    puts("6. Exit");
    printf("> ");
}

int main(void) {
    char choice[16];

    setvbuf(stdin, NULL, _IONBF, 0);
    setvbuf(stdout, NULL, _IONBF, 0);
    while (1) {
        menu();
        if (!read_line(choice, sizeof(choice))) {
            break;
        }
        if (strcmp(choice, "1") == 0) create_session();
        else if (strcmp(choice, "2") == 0) show_session();
        else if (strcmp(choice, "3") == 0) destroy_session();
        else if (strcmp(choice, "4") == 0) create_memo();
        else if (strcmp(choice, "5") == 0) check_access();
        else if (strcmp(choice, "6") == 0) break;
        else puts("Invalid choice.");
    }
    puts("Goodbye.");
    return 0;
}
