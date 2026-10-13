const { chromium } = require('C:/Users/Сергей/.codex/tools/playwright-mcp/node_modules/playwright');
const { URLSearchParams } = require('node:url');
const fs = require('node:fs');
const path = require('node:path');
const readline = require('node:readline');
const evidence = path.resolve('docs/reports/2026-10-13-jshopping-admin-browser-evidence');
fs.mkdirSync(evidence, {recursive:true});
const audit = {ajax:[],errors:[],consoleErrors:[],steps:[],saveRequests:[]};
const fields = {city:'input[name="params[city_from]"]',zip:'input[name="params[city_from_zip]"]',tariff:'select[name="sm_params[tariff_code]"]'};
let browser, page;
async function state() {
  return page.evaluate(() => {
    const point=document.querySelector('.wt-cdek-deliverypoints-field');
    const select=point?.querySelector('select');
    const info=document.querySelector('.wt-cdek-tariffinfo-field');
    const tariff=document.querySelector('select[name="sm_params[tariff_code]"]');
    return {url:location.href,title:document.title,watchfield:!!window.Joomla?.Watchfield,csrfPresent:!!window.Joomla?.getOptions('csrf.token'),
      city:document.querySelector('input[name="params[city_from]"]')?.value,zip:document.querySelector('input[name="params[city_from_zip]"]')?.value,
      point:point?{sources:JSON.parse(point.dataset.watchfield),value:select.value,count:select.options.length,status:point.querySelector('[data-role="status"]')?.textContent,
        sample:Array.from(select.options).slice(0,4).map(o=>({value:o.value,text:o.text})),fancyReady:!!point.querySelector('joomla-field-fancy-select')?.choicesInstance,
        form:select.form?.getAttribute('id'),choices:Array.from(point.querySelectorAll('.choices')).map(el=>({class:el.className,text:el.innerText.slice(0,300)}))}:null,
      tariff:tariff?{value:tariff.value,count:tariff.options.length,sample:Array.from(tariff.options).slice(0,5).map(o=>({value:o.value,text:o.text}))}:null,
      info:info?{sources:JSON.parse(info.dataset.watchfield),text:info.querySelector('[data-role="render-target"]')?.innerText}:null};
  });
}
async function saveStep(label) {
  const result={label,state:await state(),ajax:audit.ajax.slice(),errors:audit.errors.slice()};
  audit.steps.push(result);
  fs.writeFileSync(path.join(evidence,'result.json'),JSON.stringify(audit,null,2));
  return result;
}
(async()=>{
  browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
  page=await browser.newPage({viewport:{width:1440,height:1000}});
  page.setDefaultTimeout(15000);
  page.on('pageerror',err=>audit.errors.push({url:page.url(),message:err.message}));
  page.on('console',msg=>{if(msg.type()==='error')audit.consoleErrors.push({url:page.url(),message:msg.text()});});
  page.on('request',request=>{const u=new URL(request.url());if(u.pathname.includes('/administrator/')&&['save','apply'].includes(u.searchParams.get('task')))audit.saveRequests.push({method:request.method(),path:u.pathname,task:u.searchParams.get('task')});});
  page.on('response',async response=>{
    if(!response.url().includes('plugin=wtcdekdeliverypoints'))return;
    try {
      const params=new URLSearchParams(response.request().postData()||'');
      const record={status:response.status(),method:response.request().method(),address:{city:params.get('city'),postal_code:params.get('postal_code')},filters:{type:params.get('type'),is_reception:params.get('is_reception'),is_handout:params.get('is_handout')}};
      const body=await response.json();record.success=body.success;record.message=body.message;record.count=body.data?.[0]?.options?.length??null;record.sample=(body.data?.[0]?.options||[]).slice(0,3);audit.ajax.push(record);
    }catch(error){audit.ajax.push({status:response.status(),error:error.message});}
  });
  await page.goto('http://joomla.local/administrator/');
  await page.getByRole('textbox',{name:'Логин',exact:true}).fill(process.env.WT_JOOMLA_AUDIT_USER);
  await page.getByRole('textbox',{name:'Пароль',exact:true}).fill(process.env.WT_JOOMLA_AUDIT_PASSWORD);
  await page.getByRole('button',{name:'Войти',exact:true}).click();
  await page.waitForURL('**/administrator/index.php');
  await page.goto('http://joomla.local/administrator/index.php?option=com_jshopping&controller=shippingextprice&task=edit&id=4');
  await page.waitForFunction(()=>{const status=document.querySelector('.wt-cdek-deliverypoints-field [data-role="status"]');return status && !status.textContent.startsWith('Загрузка');},null,{timeout:30000}).catch(()=>{});
  console.log(JSON.stringify(await saveStep('initial')));
  const lines=readline.createInterface({input:process.stdin});
  for await(const line of lines){
    try{
      const command=JSON.parse(line);
      if(command.action==='state')console.log(JSON.stringify(await saveStep(command.label||'state')));
      else if(command.action==='fill'){
        if(!fields[command.field])throw Error('Unknown field');
        await page.locator(fields[command.field]).fill(command.value);
        if(command.blur)await page.locator(fields[command.field]).press('Tab');
        await page.waitForTimeout(1200);
        console.log(JSON.stringify(await saveStep(command.label||command.field)));
      }
      else if(command.action==='point'){
        const fancy=page.locator('.wt-cdek-deliverypoints-field joomla-field-fancy-select');
        await fancy.locator('.choices__inner').click();
        await fancy.locator('.choices__list--dropdown [data-value="'+command.value+'"]').click();
        console.log(JSON.stringify(await saveStep(command.label||'point-selected')));
      }
      else if(command.action==='goto'){
        if(!command.url.startsWith('http://joomla.local/administrator/'))throw Error('Unapproved destination');
        await page.goto(command.url);
        console.log(JSON.stringify(await saveStep(command.label||'navigation')));
      }
      else if(command.action==='links')console.log(JSON.stringify(await page.locator('a').evaluateAll(nodes=>nodes.map(a=>({text:a.innerText,title:a.title,href:a.getAttribute('href')})).filter(a=>a.href&&a.href.includes('com_jshopping')))));
      else if(command.action==='dom')console.log(JSON.stringify(await page.locator(command.selector).evaluateAll(nodes=>nodes.map(el=>({tag:el.tagName,role:el.getAttribute('role'),name:el.getAttribute('name'),text:el.innerText?.slice(0,300),html:el.outerHTML.slice(0,800)})))));
      else if(command.action==='tariff'){
        await page.locator(fields.tariff).selectOption(command.value);
        await page.waitForTimeout(500);
        console.log(JSON.stringify(await saveStep(command.label||'tariff-changed')));
      }
      else if(command.action==='screenshot'){
        const filename=String(command.name).replace(/[^a-zA-Z0-9_-]/g,'')+'.png';
        await page.screenshot({path:path.join(evidence,filename),fullPage:true});console.log(JSON.stringify({screenshot:filename}));
      }
      else if(command.action==='cancel'){
        await page.getByRole('button',{name:'Отменить',exact:true}).click();console.log(JSON.stringify(await saveStep('cancelled-without-save')));
      }
      else if(command.action==='reload'){
        await page.reload();await page.waitForTimeout(1000);console.log(JSON.stringify(await saveStep(command.label||'reloaded')));
      }
      else if(command.action==='finish'){
        fs.writeFileSync(path.join(evidence,'result.json'),JSON.stringify(audit,null,2));await browser.close();console.log(JSON.stringify({finished:true,saveRequests:audit.saveRequests.length}));lines.close();break;
      }else throw Error('Unknown action');
    }catch(error){console.log(JSON.stringify({commandError:error.message}));}
  }
})().catch(async error=>{console.error(error.message);if(browser)await browser.close();process.exitCode=1;});
