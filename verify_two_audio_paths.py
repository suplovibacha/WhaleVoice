
import firebase_admin
from firebase_admin import credentials, firestore

KEY_PATH = r"D:\Projects\HCSDLDPT\firebase_key\serviceAccountKey.json"

if not firebase_admin._apps:
    cred = credentials.Certificate(KEY_PATH)
    firebase_admin.initialize_app(cred)

db = firestore.client()

targets = [
    {
        "id": "word_00050",
        "word": "ạnh",
        "fileName": "anh5-2.wav",
        "storagePath": "A/anh5-2.wav"
    },
    {
        "id": "word_02586",
        "word": "quyến",
        "fileName": "quye+n1-2.wav",
        "storagePath": "Q/quye+n1-2.wav"
    }
]

print("===== KIEM TRA FIRESTORE =====")

for item in targets:
    doc = db.collection("words").document(item["id"]).get()

    print("\nDocument ID:", item["id"])

    if not doc.exists:
        print("KHONG TIM THAY DOCUMENT")
        continue

    data = doc.to_dict()

    print("Word:", data.get("word"))
    print("FileName:", data.get("fileName"))
    print("StoragePath:", data.get("storagePath"))

    correct = (
        data.get("word") == item["word"]
        and data.get("fileName") == item["fileName"]
        and data.get("storagePath") == item["storagePath"]
    )

    print("Trang thai:", "CHINH XAC" if correct else "CAN KIEM TRA")

print("\n===== HOAN TAT =====")
