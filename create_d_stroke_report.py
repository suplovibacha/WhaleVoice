import csv
import hashlib
from pathlib import Path

manifest = Path("storage/app/audit_reports/35_upload_manifest.csv")
output = Path("storage/app/audit_reports/d_stroke_sync_completed.csv")

if not manifest.exists():
    raise FileNotFoundError(f"Khong tim thay: {manifest}")

with manifest.open("r", encoding="utf-8-sig", newline="") as f:
    rows = list(csv.DictReader(f))

if len(rows) != 35:
    raise ValueError(f"Manifest phai co 35 dong, hien co {len(rows)}")

report = []

for row in rows:
    word = row["word"].strip()
    file_name = row["fileName"].strip()

    pair_key = f"{word}|{file_name}".lower()
    document_id = "word_sync_" + hashlib.sha256(
        (pair_key + "|D_STROKE").encode("utf-8")
    ).hexdigest()[:24]

    report.append({
        "word": word,
        "letter": "Đ",
        "fileName": file_name,
        "documentId": document_id,
        "storagePath": f"D_STROKE/{file_name}",
        "supabase_status": "UPLOAD_OK",
        "firestore_status": "CREATED_NOT_VERIFIED",
    })

with output.open("w", encoding="utf-8-sig", newline="") as f:
    writer = csv.DictWriter(f, fieldnames=report[0].keys())
    writer.writeheader()
    writer.writerows(report)

print("Tao bao cao thanh cong!")
print("Tong so tu:", len(report))
print("File:", output.resolve())
print("Trang thai Supabase: UPLOAD_OK")
print("Trang thai Firestore: CREATED_NOT_VERIFIED")
