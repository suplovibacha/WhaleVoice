import pandas as pd
import os

path = r"D:\Projects\HCSDLDPT\firebase_upload\CƠ_SỞ_DỮ_LIỆU_TIẾNG_VIỆT_đã_mã_hóa (1).xlsx"

files = [
    "anh5-2", "ba+u2",
    "cach5", "chay5", "che+4", "che+5",
    "che+nh0", "che+nh2", "che+nh3", "chinh1",
    "cong5", "coon0", "d-ia4", "d-ia5",
    "naac5", "ne+m5", "qua+n5", "qua+t5",
    "quaan2", "quach1", "quai1", "quan0", "quan1",
    "que+t1", "quye+n1", "quye+t1", "quynh1", "quyt1",
    "tro1", "un0", "un2", "un3", "uo+n1", "uo+ng1",
    "d-it5x"
]

xls = pd.ExcelFile(path)

for filename in files:
    print("\n===== FILE:", filename, "=====")
    found = False

    candidates = {filename, filename.replace("-2", "")}

    for sheet in xls.sheet_names:
        df = pd.read_excel(path, sheet_name=sheet, header=None)

        for r in range(len(df)):
            row = df.iloc[r].tolist()

            for c, value in enumerate(row):
                if isinstance(value, str) and value.strip() in candidates:
                    word = row[c - 1] if c > 0 else None
                    print("Sheet:", sheet, "| Row:", r + 1,
                          "| Word:", word, "| Code:", value.strip())
                    found = True

    if not found:
        print("KHONG TIM THAY MA TRONG EXCEL")
