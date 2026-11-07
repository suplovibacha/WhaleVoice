import csv
import json
import unicodedata
from collections import defaultdict

BASE = "storage/app/audit_reports"

EXCEL_FILE = f"{BASE}/excel_unique_pairs.csv"
OLD_FIRESTORE_FILE = "storage/app/firestore_words_audit.json"
FILE_50 = f"{BASE}/50_upload_results.csv"
FILE_35 = f"{BASE}/d_stroke_sync_completed.csv"

OUTPUT = f"{BASE}/excel_firestore_latest_comparison.csv"


def normalize(value):
    return unicodedata.normalize(
        "NFC",
        str(value or "").strip().lower()
    )


def read_csv(path):
    with open(path, "r", encoding="utf-8-sig", newline="") as f:
        return list(csv.DictReader(f))


# 1. Load Excel
excel = read_csv(EXCEL_FILE)

excel_map = defaultdict(list)

for row in excel:
    key = (
        normalize(row["word_normalized"]),
        normalize(row["fileName"])
    )
    excel_map[key].append(row)


# 2. Load old Firestore audit
with open(OLD_FIRESTORE_FILE, "r", encoding="utf-8") as f:
    old_firestore = json.load(f)

firestore_map = {}

for row in old_firestore:
    key = (
        normalize(row["word"]),
        normalize(row["fileName"])
    )
    firestore_map[key] = {
        "id": row["id"],
        "word": row["word"],
        "fileName": row["fileName"],
        "storagePath": row["storagePath"],
        "source": "OLD_AUDIT"
    }


# 3. Add 50 verified documents
rows50 = read_csv(FILE_50)

if len(rows50) != 50:
    raise ValueError(f"Expected 50 rows, got {len(rows50)}")

for row in rows50:
    if row.get("status", "").upper() != "UPLOAD_OK":
        raise ValueError(
            f"Unexpected upload status: {row}"
        )

    key = (
        normalize(row["word"]),
        normalize(row["fileName"])
    )

    firestore_map[key] = {
        "id": "",
        "word": row["word"],
        "fileName": row["fileName"],
        "storagePath": row["storagePath"],
        "source": "VERIFIED_50"
    }


# 4. Add 35 verified D-stroke documents
rows35 = read_csv(FILE_35)

if len(rows35) != 35:
    raise ValueError(f"Expected 35 rows, got {len(rows35)}")

for row in rows35:
    key = (
        normalize(row["word"]),
        normalize(row["fileName"])
    )

    firestore_map[key] = {
        "id": row["documentId"],
        "word": row["word"],
        "fileName": row["fileName"],
        "storagePath": row["storagePath"],
        "source": "VERIFIED_35"
    }


# 5. Compare
excel_keys = set(excel_map)
firestore_keys = set(firestore_map)

both = excel_keys & firestore_keys
only_excel = excel_keys - firestore_keys
only_firestore = firestore_keys - excel_keys

print("===== LATEST OFFLINE COMPARISON =====")
print("Excel pairs:", len(excel_keys))
print("Old Firestore documents:", len(old_firestore))
print("Combined Firestore pairs:", len(firestore_keys))
print("Pairs in both:", len(both))
print("Only in Excel:", len(only_excel))
print("Only in Firestore:", len(only_firestore))


# 6. Export complete comparison
with open(OUTPUT, "w", encoding="utf-8-sig", newline="") as f:
    writer = csv.writer(f)

    writer.writerow([
        "word",
        "fileName",
        "excel_status",
        "firestore_status",
        "firestore_id",
        "storagePath",
        "source"
    ])

    for key in sorted(excel_keys | firestore_keys):
        excel_rows = excel_map.get(key, [])
        firestore_row = firestore_map.get(key)

        word = (
            excel_rows[0]["word_normalized"]
            if excel_rows else firestore_row["word"]
        )

        filename = (
            excel_rows[0]["fileName"]
            if excel_rows else firestore_row["fileName"]
        )

        writer.writerow([
            word,
            filename,
            "FOUND" if excel_rows else "NOT_IN_EXCEL",
            "FOUND" if firestore_row else "NOT_FOUND",
            firestore_row["id"] if firestore_row else "",
            firestore_row["storagePath"] if firestore_row else "",
            firestore_row["source"] if firestore_row else ""
        ])


print("\nReport saved:", OUTPUT)