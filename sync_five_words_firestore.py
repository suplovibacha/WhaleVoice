
import firebase_admin
from firebase_admin import credentials, firestore
from pathlib import Path
import sys

sys.stdout.reconfigure(encoding="utf-8")

CREDENTIAL_PATH = (
    r"D:\Projects\HCSDLDPT\firebase_key\serviceAccountKey.json"
)

LOCAL_ROOT = Path(r"D:\Projects\HCSDLDPT\source")

# False: kiểm tra trước
# True: ghi dữ liệu vào Firestore
APPLY_CHANGES = True

NEW_WORDS = [
    {
        "word": "mỹ",
        "letter": "M",
        "fileName": "my4.wav",
        "storagePath": "my4.wav"
    },
    {
        "word": "vía",
        "letter": "V",
        "fileName": "via1.wav",
        "storagePath": "via1.wav"
    },
    {
        "word": "vô",
        "letter": "V",
        "fileName": "vo+0.wav",
        "storagePath": "vo+0.wav"
    },
    {
        "word": "nhật",
        "letter": "N",
        "fileName": "nha+t5.wav",
        "storagePath": "nha+t5.wav"
    },
    {
        "word": "giai",
        "letter": "G",
        "fileName": "giai0.wav",
        "storagePath": "giai0.wav"
    }
]

cred = credentials.Certificate(CREDENTIAL_PATH)

if not firebase_admin._apps:
    firebase_admin.initialize_app(cred)

db = firestore.client()
collection = db.collection("words")

print("\n===== KIEM TRA VA DONG BO =====")

for item in NEW_WORDS:
    word = item["word"]
    filename = item["fileName"]
    local_path = LOCAL_ROOT / item["storagePath"]

    print(f"\n--- {word} ---")

    if not local_path.is_file() or local_path.stat().st_size == 0:
        print("LOI: File WAV khong ton tai hoac rong")
        continue

    word_docs = list(
        collection.where("word", "==", word).stream()
    )

    file_docs = list(
        collection.where("fileName", "==", filename).stream()
    )

    if word_docs or file_docs:
        print("BO QUA: Phat hien ban ghi trung")

        for doc in word_docs + file_docs:
            print(doc.id, doc.to_dict())

        continue

    print("FILE WAV: OK")
    print("DU LIEU:", item)

    if APPLY_CHANGES:
        collection.add(item)
        print("DA THEM VAO FIRESTORE")
    else:
        print("CHE DO KIEM TRA - CHUA GHI")

print("\n===== HOAN TAT =====")
