
import pandas as pd

EXCEL = r"D:\Projects\HCSDLDPT\CƠ_SỞ_DỮ_LIỆU_TIẾNG_VIỆT_đã_mã_hóa.xlsx"

targets = {"quật", "quết"}

sheets = pd.read_excel(
    EXCEL,
    sheet_name=None,
    header=None,
    dtype=str
)

print("===== TIM QUAT VA QUET TRONG EXCEL =====")

for sheet_name, df in sheets.items():
    for row_index, row in df.iterrows():
        values = [
            str(value).strip()
            for value in row
            if pd.notna(value)
        ]

        row_text = " | ".join(values).lower()

        if any(word in row_text for word in targets):
            print("\nSheet:", sheet_name)
            print("Dong:", row_index + 1)
            print(" | ".join(values))

print("\n===== HOAN TAT =====")
