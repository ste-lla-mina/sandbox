#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#define FILE_NAME "ubudehe.txt"
#define TEMP_FILE "temp.txt"

typedef struct {
    char nationalID[20];
    char gender[10];
    char phone[15];
    char cell[30];
    char village[30];
    char startingDate[15];
} Citizen;

void addRecord();
void viewRecords();
void editRecord();
void deleteRecord();
void clearInputBuffer();

int main() {
    int choice;

    while (1) {
        printf("\n Ubudehe Management System. \n");
        printf("1. Add New Record\n");
        printf("2. View All Records\n");
        printf("3. Edit Record\n");
        printf("4. Delete Record\n");
        printf("5. Exit\n");
        printf("Enter your choice: ");
       scanf("%d",&choice);

        switch (choice) {
            case 1: addRecord(); break;
            case 2: viewRecords(); break;
            case 3: editRecord(); break;
            case 4: deleteRecord(); break;
            case 5: printf("Exiting ....\n"); exit(0);
            default: printf("Invalid choice! Please try again.\n");
        }
    }
    return 0;
}

void addRecord() {
    FILE *file = fopen(FILE_NAME, "a");
    if (file == NULL) {
        printf("Error opening file!\n");
        return;
    }

    Citizen c;
    printf("\n--- Add New Record ---\n");
    printf("Enter National ID: "); scanf("%s", c.nationalID);
    printf("Enter Gender: "); scanf("%s", c.gender);
    printf("Enter Phone Number: "); scanf("%s", c.phone);
    printf("Enter Cell: "); scanf("%s", c.cell);
    printf("Enter Village: "); scanf("%s", c.village);
    printf("Enter Starting Date (DD-MM-YYYY): "); scanf("%s", c.startingDate);

    fprintf(file, "%s %s %s %s %s %s\n", c.nationalID, c.gender, c.phone, c.cell, c.village, c.startingDate);
    fclose(file);

    printf("Record added successfully!\n");
}

void viewRecords() {
    FILE *file = fopen(FILE_NAME, "r");
    if (file == NULL) {
        printf("\nNo records found.\n");
        return;
    }

    Citizen c;
    printf("\n---------------------------------- UBUDEHE RECORDS. ----------------------------------\n");
    printf("%-20s %-10s %-15s %-15s %-15s %-15s\n", "National ID", "Gender", "Phone", "Cell", "Village", "Start Date");
    printf("-------------------------------------------------------------------------------------\n");

    while (fscanf(file, "%s %s %s %s %s %s", c.nationalID, c.gender, c.phone, c.cell, c.village, c.startingDate) != EOF) {
        printf("%-20s %-10s %-15s %-15s %-15s %-15s\n", c.nationalID, c.gender, c.phone, c.cell, c.village, c.startingDate);
    }
    printf("-------------------------------------------------------------------------------------\n");
    fclose(file);
}

void editRecord() {
    FILE *file = fopen(FILE_NAME, "r");
    FILE *temp = fopen(TEMP_FILE, "w");

    if (file == NULL || temp == NULL) {
        printf("Error processing files!\n");
        return;
    }

    char targetID[20];
    int found = 0;
    Citizen c;

    printf("\nEnter National ID of the record to edit: ");
    scanf("%s", targetID);

    while (fscanf(file, "%s %s %s %s %s %s", c.nationalID, c.gender, c.phone, c.cell, c.village, c.startingDate) != EOF) {
        if (strcmp(c.nationalID, targetID) == 0) {
            found = 1;
            printf("\nRecord Found! Enter New Details:\n");
            printf("Enter New Gender: "); scanf("%s", c.gender);
            printf("Enter New Phone Number: "); scanf("%s", c.phone);
            printf("Enter New Cell: "); scanf("%s", c.cell);
            printf("Enter New Village: "); scanf("%s", c.village);
            printf("Enter New Starting Date (DD-MM-YYYY): "); scanf("%s", c.startingDate);
        }
        fprintf(temp, "%s %s %s %s %s %s\n", c.nationalID, c.gender, c.phone, c.cell, c.village, c.startingDate);
    }

    fclose(file);
    fclose(temp);

    remove(FILE_NAME);
    rename(TEMP_FILE, FILE_NAME);

    if (found) {
        printf("Record updated successfully!\n");
    } else {
        printf("Record with ID %s not found.\n", targetID);
    }
}

void deleteRecord() {
    FILE *file = fopen(FILE_NAME, "r");
    FILE *temp = fopen(TEMP_FILE, "w");

    if (file == NULL || temp == NULL) {
        printf("Error processing files!\n");
        return;
    }

    char targetID[20];
    int found = 0;
    Citizen c;

    printf("\nEnter National ID of the record to delete: ");
    scanf("%s", targetID);

    while (fscanf(file, "%s %s %s %s %s %s", c.nationalID, c.gender, c.phone, c.cell, c.village, c.startingDate) != EOF) {
        if (strcmp(c.nationalID, targetID) == 0) {
            found = 1;
            continue;
        }
        fprintf(temp, "%s %s %s %s %s %s\n", c.nationalID, c.gender, c.phone, c.cell, c.village, c.startingDate);
    }

    fclose(file);
    fclose(temp);

    remove(FILE_NAME);
    rename(TEMP_FILE, FILE_NAME);

    if (found) {
        printf("Record deleted successfully!\n");
    } else {
        printf("Record with ID %s not found.\n", targetID);
    }
}
