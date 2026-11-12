
import firebase_admin
from firebase_admin import credentials, firestore
from pathlib import Path
import sys

sys.stdout.reconfigure(encoding="utf-8")

CREDENTIAL_PATH = Path(
    r"D:\Projects\HCSDLDPT\firebase_key\serviceAccountKey.json"
)

LOCAL_ROOT = Path(r"D:\Projects\HCSDLDPT\source")

# False: chỉ kiểm tra
# True: cập nhật Firestore
APPLY_CHANGES = True

WORDS = {
    "mỹ": "M/my4.wav",
    "vía": "V/via1.wav",
    "vô": "V/vo+0.wav",
    "nhật": "N/nha+t5.wav",
    "giai": "G/giai0.wav",
}

cred = credentials.Certificate(str(CREDENTIAL_PATH))

if not firebase_admin._apps:
    firebase_admin.initialize_app(cred)

db = firestore.client()
collection = db.collection("words")

print("\n===== CAP NHAT STORAGE PATH =====")

for word, new_path in WORDS.items():
    print(f"\n--- {word} ---")

    local_file = LOCAL_ROOT / new_path

    if not local_file.is_file() or local_file.stat().st_size == 0:
        print("LOI: Khong tim thay file WAV:", local_file)
        continue

    docs = list(
        collection.where("word", "==", word).stream()
    )

    if len(docs) != 1:
        print(f"LOI: Tim thay {len(docs)} document cho tu '{word}'")
        continue

    doc = docs[0]
    old_path = doc.to_dict().get("storagePath")

    print("Document ID:", doc.id)
    print("Duong dan cu:", old_path)
    print("Duong dan moi:", new_path)
    print("File WAV: OK")

    if APPLY_CHANGES:
        doc.reference.update({"storagePath": new_path})
        print("DA CAP NHAT FIRESTORE")
    else:
        print("CHE DO KIEM TRA - CHUA CAP NHAT")

print("\n===== HOAN TAT =====")

