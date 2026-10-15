import datetime
import hashlib
import json
import pathlib
import re

HERE=pathlib.Path(__file__).resolve().parent
ROOT=HERE.parents[2]
tasks_root=ROOT/'docs/tasks/2026-10-15-prerelease'
records=json.loads((tasks_root/'tasks.json').read_text(encoding='utf-8'))
release='docs/release/2026-10-15-1.4.0-style-package.md'
for task in records:
    if task['id']=='REL-007':
        task['stage']='style-complete-types-next-release'
        task['constraints']=[x for x in task['constraints'] if x!='Implementation has not started']
        task['constraints'].append('Style part completed and packaged on 2026-10-15; PHPDoc/type cleanup deferred to next release')
    elif task['id']!='REL-001':
        task['stage']='planned-next-release'
        task['constraints'].append('Operator deferred this task to the next release; version not assigned')
    if release not in task['artifacts']:
        task['artifacts'].append(release)
    path=ROOT/task['artifacts'][0]
    text=path.read_text(encoding='utf-8')
    if task['id']=='REL-001':
        text=text.replace('Статус: **Выполнено в исходниках**','Статус: **Выполнено в исходниках и включено в пакет 1.4.0**')
        text=text.replace('- [ ] Пересборка ZIP и полноценная установка относятся к отдельной доставке; текущий ZIP содержит прежний установщик.', '- [x] Пересобран ZIP 1.4.0 с исправленным installer, проверены executable tokens и PHP 7.4 lint.\n- [ ] Полная установка/обновление/удаление на реальном стенде — отдельная приёмка, не проверка синтаксиса.')
    elif task['id']=='REL-007':
        text=text.replace('Статус: **Открыта**','Статус: **Style-часть выполнена; PHPDoc/type cleanup — следующий релиз**')
        text += '\n## Обновление после форматирования\n\nJoomla/PSR-12 выполнен: PHPCS 0 errors/warnings, CS Fixer dry-run без изменений, PHP 7.4 lint 40/40. Imports/отступы/braces/EOL исправлены. PHPStan сохраняет прежние 32 сообщения; оставшийся type/PHPDoc cleanup отложен оператором. [Текущая проверка](../../reports/2026-10-15-style-package-verification-checklist.md).\n'
    else:
        text += '\n## Планирование релиза\n\nОператор перенёс эту задачу на следующий релиз 15.10.2026; номер версии ещё не назначен. Реализация не начата. Места/строки исходного аудита относятся к срезу до форматирования. [Текущий пакет](../../release/2026-10-15-1.4.0-style-package.md).\n'
    path.write_text(text,encoding='utf-8')
(tasks_root/'tasks.json').write_text(json.dumps(records,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
index=(tasks_root/'index.md').read_text(encoding='utf-8')
index=index.replace('| P1 | Выполнено в исходниках |','| P1 | Выполнено и включено в пакет 1.4.0 |',1)
index=index.replace('| P3 | Открыта |','| P3 | Style выполнен; PHPDoc/types — следующий релиз |',1)
start=index.index('Из 13 задач:')
end=index.index('[tasks.json]',start)
index=index[:start]+'''Из 13 задач REL-001 выполнена и включена в новый пакет; в REL-007 завершён style, PHPDoc/type cleanup остаётся. Остальные 11 задач, а также остаток REL-007, оператор перенёс на следующий релиз; номер версии ещё не назначен. REL-008/F5 — будущая совместимость и решение lifecycle, не подтверждённый отказ обычной Joomla 4/5/6.

Текущий пакет 1.4.0 уже пересобран после Joomla/PSR-12 форматирования и содержит исправленный PHP 7.4 installer. [Пакет и проверки](../../release/2026-10-15-1.4.0-style-package.md). Установка/публикация не выполнялись.

Порядок следующего релиза: REL-002/REL-003, REL-004/REL-005/REL-006, F4/F3 и адресное повторение F1/F2; остаток REL-007. REL-008/F5 можно планировать отдельно. Реализация отложенных задач автоматически не запускается.

'''+index[end:]
(tasks_root/'index.md').write_text(index,encoding='utf-8')
context=ROOT/'.webtolk/context/project-context.yaml'
text=context.read_text(encoding='utf-8')
updates={'session_state':'local-style-package-complete-2026-10-15','continuation':'.webtolk/continuations/2026-10-15-style-package.md','fields_work_state':'deferred-to-next-release-by-operator-2026-10-15','latest_local_package_report':release,'latest_local_package_sha256':'45cd8942f90233060c89b8236c751f62259f31753c7a28d1b67ee454b85640f9','latest_local_package_status':'1.4.0-rebuilt-joomla-psr12-php74-2026-10-15-not-installed-not-published'}
for key,value in updates.items():
    text,n=re.subn(r'(?m)^(    '+re.escape(key)+r':).*$','\\1 '+value,text,count=1)
    assert n==1,key
context.write_text(text,encoding='utf-8')
continuation='''# Продолжение: style/package 15.10.2026

Latest scope оператора: функциональные дефекты — задачи следующего релиза; сейчас Joomla/PSR-12 и штатная сборка. Не возобновлять функциональные fixes без новой задачи.

Ветка dev, HEAD 38e43d8, локальные изменения не закоммичены. Отформатировано 39 PHP; один task provider уже соответствовал. PHP 7.4 installer сохранён. PHPCS 0/0, CS Fixer идемпотентен, lint PHP 7.4/8.0/8.1/8.3 по 40/40, normalized AST 40/40, native Joomla 4/5/6 540 passed, PhpStorm ERROR 0 на 39 файлах. PHPStan32/три failing regression tests остаются на следующий релиз, без новых diagnostic identifiers.

Пакет .packages/WT Cdek library package_1.4.0.zip: 384273 bytes, 66 entries, SHA256 45cd8942f90233060c89b8236c751f62259f31753c7a28d1b67ee454b85640f9. Штатный phing.xml target 3. Package release; source-preserving staging проверен. Архивные PHP tokens равны source, PHP7.4 lint40/JS5/manifests4 passed. Не установлен/не опубликован; commit/push не было.

Старый ZIP и source-before сохранены E:/tmp/wtcdek-style-package-20261015. Предшествующая cleanup E:/tmp/wtcdek-prerelease-cleanup-20261015 содержит перенесённые старые .webtolk/tmp и .packages unpacked trees.

Current report docs/release/2026-10-15-1.4.0-style-package.md; checklist docs/reports/2026-10-15-style-package-verification-checklist.md; tasks docs/tasks/2026-10-15-prerelease/index.md. REL-001 выполнена и packaged; REL-007 style выполнен, type/PHPDoc часть отложена; остальные 11 — следующий релиз, версия не назначена. Native .pf отсутствует, onboarding/ledger не имитировать.
'''
(ROOT/'.webtolk/continuations/2026-10-15-style-package.md').write_text(continuation,encoding='utf-8')
log={'timestamp':datetime.datetime.now(datetime.timezone.utc).isoformat(),'task':'joomla-psr12-style-package-20261015','tool_name':'PF shared PHP QA + PhpStorm MCP + Phing packager','operation':'source formatting, assurance, local source-preserving 1.4.0 package','fallback':True,'reason':'Serena not exposed; project has .webtolk but no .pf descriptor; registered PHP tools and existing phing.xml bridge used','status':'completed-local-package','notes':'PHPCS0/0; Fixer clean; PHP7.4/8.0/8.1/8.3 40/40; AST40; native540; IDE ERROR0. Functional tasks deferred by operator. PHPStan32/deferred PHPUnit3 failures preserved. No provider/DB/install/publish/commit/push/shared-tool changes.'}
with (ROOT/'.webtolk/logs/tool-telemetry.ndjson').open('a',encoding='utf-8') as file:
    file.write(json.dumps(log,ensure_ascii=False)+'\n')
print('Task statuses, context, continuation and telemetry updated')
