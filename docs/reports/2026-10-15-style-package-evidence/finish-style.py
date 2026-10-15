import json
import pathlib
import re
import textwrap

HERE = pathlib.Path(__file__).resolve().parent
ROOT = HERE.parents[2]
report=json.loads((HERE/'phpcs-after-autofix.stdout').read_text(encoding='utf-8-sig'))
patch='*** Begin Patch\n'
changes=[]
for name, result in report['files'].items():
    file=pathlib.Path(name)
    rel=str(file.relative_to(ROOT)).replace('\\','/')
    text=file.read_text(encoding='utf-8-sig')
    if any(m['source']=='PSR1.Files.SideEffects.FoundWithSymbols' for m in result['messages']):
        guard=re.search(r"(?m)^\\?defined\('_JEXEC'\) or die;$",text)
        assert guard,rel
        old=guard.group(0)
        new='// phpcs:disable PSR1.Files.SideEffects\n'+old+'\n// phpcs:enable PSR1.Files.SideEffects'
        patch+='*** Update File: '+str(file).replace('\\','/')+'\n@@\n-'+old+'\n+'+new.replace('\n','\n+')+'\n'
        changes.append({'file':rel,'change':'Joomla core annotation around _JEXEC guard'})
    if any(m['source']=='PSR1.Methods.CamelCapsMethodName.NotCamelCaps' for m in result['messages']):
        assert text.count("'UpdateWtCdekData'")==1
        assert text.count('function UpdateWtCdekData(')==1
        patch+='*** Update File: '+str(file).replace('\\','/')+"\n@@\n-            'method'          => 'UpdateWtCdekData',\n+            'method'          => 'updateWtCdekData',\n@@\n-    private function UpdateWtCdekData(ExecuteTaskEvent $event): int\n+    private function updateWtCdekData(ExecuteTaskEvent $event): int\n"
        changes.append({'file':rel,'change':'lowerCamelCase private routine and matching TASKS_MAP entry'})
    if any(m['source']=='Generic.Files.LineLength.MaxExceeded' for m in result['messages']):
        lines=text.splitlines()
        long=next(line for line in lines if len(line)>560)
        assert '<path ' in long and ' d="' in long
        start,rest=long.split(' d="',1)
        data,end=rest.split('"',1)
        # Adjacent literal concatenation preserves every SVG path byte.
        chunks=[data[i:i+400] for i in range(0,len(data),400)]
        new=start+' d="' + "'\n" + ''.join("                . '"+chunk+"'\n" for chunk in chunks) + "                . '\""+end
        patch+='*** Update File: '+str(file).replace('\\','/')+'\n@@\n-'+long+'\n+'+new.replace('\n','\n+')+'\n'
        changes.append({'file':rel,'change':'split literal SVG path into concatenated fragments; bytes unchanged'})
patch+='*** End Patch\n'
(HERE/'finish-style.patch').write_text(patch,encoding='utf-8')
(HERE/'manual-style-changes.json').write_text(json.dumps(changes,indent=2),encoding='utf-8')
print('Prepared',len(changes),'style-only adjustments')
