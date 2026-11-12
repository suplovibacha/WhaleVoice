
import firebase_admin
from firebase_admin import credentials, firestore

KEY_PATH = r"D:\Projects\HCSDLDPT\firebase_key\serviceAccountKey.json"

if not firebase_admin._apps:
    cred = credentials.Certificate(KEY_PATH)
    firebase_admin.initialize_app(cred)

db = firestore.client()

updates = [
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

batch = db.batch()

for item in updates:
    doc_ref = db.collection("words").document(item["id"])

    batch.update(doc_ref, {
        "fileName": item["fileName"],
        "storagePath": item["storagePath"]
    })

batch.commit()

print("Da cap nhat thanh cong 2 document:")
for item in updates:
    print(
        item["id"],
        "|", item["word"],
        "|", item["storagePath"]
    )
