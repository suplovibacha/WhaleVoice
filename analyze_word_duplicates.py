import csv
import json
import unicodedata
from collections import defaultdict

excel_path = "storage/app/audit_reports/excel_unique_pairs.csv"
firestore_path = "storage/app/firestore_words_audit.json"

with open(excel_path, "r", encoding="utf-8-sig", newline="") as f:
    excel = list(csv.DictReader(f))

with open(firestore_path, "r", encoding="utf-8") as f:
    firestore = json.load(f)


def normalize(text):
    return unicodedata.normalize("NFC", (text or "").strip().lower())


# Group Excel by storage filename
excel_groups = defaultdict(list)

for item in excel:
    key = normalize(item["word_normalized"]), normalize(item["fileName"])
    excel_groups[key].append(item)


# Group Firestore by word + filename
firestore_groups = defaultdict(list)

for item in firestore:
    key = normalize(item["word"]), normalize(item["fileName"])
    firestore_groups[key].append(item)


print("===== DUPLICATE EXCEL PAIRS =====")

duplicates_excel = {
    key: items for key, items in excel_groups.items()
    if len(items) > 1
}

print("Duplicate groups:", len(duplicates_excel))

for key, items in list(duplicates_excel.items())[:20]:
    print(key)
    for item in items:
        print(" ", item["sheet"], item["word"], item["fileName"])


print("\n===== DUPLICATE FIRESTORE PAIRS =====")

duplicates_firestore = {
    key: items for key, items in firestore_groups.items()
    if len(items) > 1
}

print("Duplicate groups:", len(duplicates_firestore))

for key, items in duplicates_firestore.items():
    print(key)
    for item in items:
        print(" ", item["id"], item["word"], item["storagePath"])


print("\n===== OVERALL COMPARISON =====")

excel_keys = set(excel_groups)
firestore_keys = set(firestore_groups)

print("Excel unique pairs:", len(excel_keys))
print("Firestore unique pairs:", len(firestore_keys))
print("Pairs in both:", len(excel_keys & firestore_keys))
print("Pairs only in Excel:", len(excel_keys - firestore_keys))
print("Pairs only in Firestore:", len(firestore_keys - excel_keys))