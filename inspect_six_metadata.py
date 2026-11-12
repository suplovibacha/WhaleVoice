import firebase_admin
from firebase_admin import credentials, firestore
from google.cloud.firestore_v1.base_query import FieldFilter

KEY_PATH = r"D:\Projects\HCSDLDPT\firebase_key\serviceAccountKey.json"

if not firebase_admin._apps:
    cred = credentials.Certificate(KEY_PATH)
    firebase_admin.initialize_app(cred)

db = firestore.client()

words = ["bầu", "nệm", "tró", "địt", "quật", "quết"]

print("===== KIEM TRA 6 TU TREN FIRESTORE =====")

query = (
    db.collection("words")
    .where(filter=FieldFilter("word", "in", words))
)

found = set()

for doc in query.stream():
    data = doc.to_dict()
    word = data.get("word", "")
    found.add(word)

    print("\n--------------------------------")
    print("Document ID:", doc.id)
    print("Word:", word)
    print("Letter:", data.get("letter"))
    print("FileName:", data.get("fileName"))
    print("StoragePath:", data.get("storagePath"))

print("\n===== TONG KET =====")

for word in words:
    print("[DA CO]" if word in found else "[CHUA TIM THAY]", word)

print("\n===== HOAN TAT =====")
