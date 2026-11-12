import firebase_admin
from firebase_admin import credentials, firestore

KEY_PATH = r"D:\Projects\HCSDLDPT\firebase_key\serviceAccountKey.json"

if not firebase_admin._apps:
    cred = credentials.Certificate(KEY_PATH)
    firebase_admin.initialize_app(cred)

db = firestore.client()
collection = db.collection("words")

print("===== KIEM TRA TU MO TRONG FIRESTORE =====")

found = False

# Tìm theo từ
docs_by_word = collection.where(
    "word", "==", "mô"
).stream()

for doc in docs_by_word:
    found = True
    print("\nTim thay theo word:")
    print("Document ID:", doc.id)
    print(doc.to_dict())

# Tìm theo tên file
docs_by_file = collection.where(
    "fileName", "==", "mo+0.wav"
).stream()

for doc in docs_by_file:
    found = True
    print("\nTim thay theo fileName:")
    print("Document ID:", doc.id)
    print(doc.to_dict())

if not found:
    print("\nCHUA CO DU LIEU MO TRONG FIRESTORE")
    