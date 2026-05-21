import os

directory = r"c:\xampp\htdocs\university-project"
extensions = ('.php', '.html', '.js', '.css', '.env')

for root, dirs, files in os.walk(directory):
    for file in files:
        if file.endswith(extensions):
            filepath = os.path.join(root, file)
            try:
                with open(filepath, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                new_content = content.replace('FixIt Hub', 'AsaanFix')
                new_content = new_content.replace('FixItHub', 'AsaanFix')
                new_content = new_content.replace('fixithub.pk', 'asaanfix.pk')
                
                if new_content != content:
                    with open(filepath, 'w', encoding='utf-8') as f:
                        f.write(new_content)
            except Exception as e:
                pass
