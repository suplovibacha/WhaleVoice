import firebase_admin
from firebase_admin import credentials, firestore

KEY_PATH = r"D:\Projects\HCSDLDPT\firebase_key\serviceAccountKey.json"

if not firebase_admin._apps:
    cred = credentials.Certificate(KEY_PATH)
    firebase_admin.initialize_app(cred)

db = firestore.client()
collection = db.collection("words")

word = "mô"
file_name = "mo+0.wav"

# Kiểm tra lại để tránh tạo trùng
by_word = list(collection.where("word", "==", word).stream())
by_file = list(collection.where("fileName", "==", file_name).stream())

if by_word or by_file:
    print("DA TON TAI DU LIEU, KHONG TAO MOI.")
    for doc in by_word + by_file:
        print("Document ID:", doc.id)
        print(doc.to_dict())
else:
    data = {
        "word": word,
        "letter": "M",
        "fileName": file_name,
        "storagePath": "M/mo+0.wav"
    }

    new_doc = collection.document()
    new_doc.set(data)

    print("DA THEM THANH CONG!")
    print("Document ID:", new_doc.id)
    print("Du lieu:", data)