import pandas as pd

path = r"D:\Projects\HCSDLDPT\firebase_upload\CƠ_SỞ_DỮ_LIỆU_TIẾNG_VIỆT_đã_mã_hóa (1).xlsx"
xls = pd.ExcelFile(path)

targets = ["bầu", "nệm", "tró"]

for sheet in xls.sheet_names:
    df = pd.read_excel(path, sheet_name=sheet, header=None)

    for r in range(len(df)):
        row = df.iloc[r].tolist()

        for i, value in enumerate(row):
            if isinstance(value, str) and value.strip() in targets:
                print("--------------------------------")
                print("Sheet:", sheet)
                print("Row:", r + 1)
                print("Word:", value.strip())
                print("Code:", row[i + 1] if i + 1 < len(row) else None)
