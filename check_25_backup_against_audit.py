
import json
from pathlib import Path

PROJECT = Path(r"D:\Projects\HCSDLDPT\WhaleVoice")
SOURCE = Path(r"D:\Projects\HCSDLDPT\source")
BACKUP = Path(r"D:\Projects\HCSDLDPT\backup_wav_2")
AUDIT = PROJECT / "storage" / "app" / "firestore_words_audit.json"

UPDATED_FILES = {
    "anh5.wav",
    "quye+n1.wav"
}

def collect_records(data):
    """Tìm các bản ghi có fileName và storagePath trong JSON."""
    records = []

    if isinstance(data, dict):
        if "fileName" in data and "storagePath" in data:
            records.append(data)

        for value in data.values():
            records.extend(collect_records(value))

    elif isinstance(data, list):
        for value in data:
            records.extend(collect_records(value))

    return records


print("===== KIEM TRA 25 FILE VOI AUDIT CU =====")

if not AUDIT.exists():
    raise FileNotFoundError(f"Khong tim thay audit: {AUDIT}")

with open(AUDIT, "r", encoding="utf-8-sig") as f:
    audit_data = json.load(f)

records = collect_records(audit_data)

backup_files = [
    p for p in BACKUP.rglob("*.wav")
    if p.name not in UPDATED_FILES
]

matched = 0
not_matched = 0
missing_source = 0
ambiguous = 0

for backup_file in sorted(backup_files):
    # Bỏ hậu tố -2 để tìm bản tương ứng trong source
    stem = backup_file.stem

    if not stem.endswith("-2"):
        print(f"\nBO QUA (khong co hau to -2): {backup_file.name}")
        continue

    original_name = stem[:-2] + backup_file.suffix

    candidates = [
        p for p in SOURCE.rglob(original_name)
        if p.is_file()
    ]

    print("\n--------------------------------")
    print("File backup:", backup_file.name)
    print("File doi ung:", original_name)

    if not candidates:
        print("KET QUA: KHONG TIM THAY FILE TRONG SOURCE")
        missing_source += 1
        continue

    if len(candidates) > 1:
        print("KET QUA: CO NHIEU FILE CUNG TEN TRONG SOURCE")
        for p in candidates:
            print("  -", p.relative_to(SOURCE).as_posix())
        ambiguous += 1
        continue

    source_file = candidates[0]
    expected_path = source_file.relative_to(SOURCE).as_posix()

    print("Source path:", expected_path)

    exact_records = [
        r for r in records
        if r.get("fileName") == original_name
        and str(r.get("storagePath", "")).replace("\\", "/") == expected_path
    ]

    if exact_records:
        print("KET QUA: AUDIT KHOP VOI FILE SOURCE")
        for r in exact_records:
            print("Document ID:", r.get("id"))
            print("Word:", r.get("word"))
        matched += 1
        continue

    same_name_records = [
        r for r in records
        if r.get("fileName") == original_name
    ]

    if same_name_records:
        print("KET QUA: TEN FILE CO TRONG AUDIT NHUNG STORAGEPATH KHAC")
        for r in same_name_records:
            print("Document ID:", r.get("id"))
            print("Word:", r.get("word"))
            print("Audit path:", r.get("storagePath"))
    else:
        print("KET QUA: KHONG TIM THAY FILE TRONG AUDIT")

    not_matched += 1

print("\n===== TONG KET =====")
print("So file backup da kiem tra:", len(backup_files))
print("Audit khop voi source:", matched)
print("Khong khop hoac khong co trong audit:", not_matched)
print("Khong tim thay file source:", missing_source)
print("Nhieu file source trung ten:", ambiguous)

print("\nLuu y: Ket qua chi doi chieu audit JSON cu,")
print("khong phai doc truc tiep du lieu Firestore hien tai.")
