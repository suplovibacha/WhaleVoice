import json
from collections import Counter, defaultdict

path = "storage/app/firestore_words_audit.json"

with open(path, "r", encoding="utf-8") as f:
    data = json.load(f)

print("===== DUPLICATE STORAGE PATHS =====")

groups = defaultdict(list)

for item in data:
    groups[item.get("storagePath", "")].append(item)

duplicates = {
    key: value
    for key, value in groups.items()
    if len(value) > 1
}

print("Duplicate groups:", len(duplicates))

for storage_path, items in duplicates.items():
    print("\nPATH:", storage_path.encode("unicode_escape").decode())

    for item in items:
        print(json.dumps(item, ensure_ascii=True, indent=2))


print("\n===== LETTER VALUES =====")

letters = Counter(item.get("letter", "") for item in data)

for letter, count in sorted(letters.items()):
    print(
        f"{letter.encode('unicode_escape').decode()}: {count}"
    )


print("\n===== SAMPLE WORDS WITH NON-ASCII LETTER =====")

shown = 0

for item in data:
    letter = item.get("letter", "")

    if any(ord(c) > 127 for c in letter):
        print(json.dumps(item, ensure_ascii=True))
        shown += 1

        if shown >= 10:
            break