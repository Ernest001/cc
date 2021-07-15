import json

def file_get_contents(filename):
    with open(filename) as f:
        return f.read()

file_contents = file_get_contents("/home/ernest/work/Akash/common-crawl/dev-utils/text.json")

json_contents = json.loads(file_contents)

print(json_contents)