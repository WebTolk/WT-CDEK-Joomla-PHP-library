const fs=require('node:fs');
(async()=>{
 const tabs=await(await fetch('http://127.0.0.1:9340/json/list')).json();
 const ws=new WebSocket(tabs.find(tab=>tab.type==='page').webSocketDebuggerUrl);
 await new Promise(resolve=>ws.onopen=resolve);
 let id=0;const pending=new Map();const errors=[];
 const call=(method,params={})=>new Promise((resolve,reject)=>{const key=++id;pending.set(key,{resolve,reject});ws.send(JSON.stringify({id:key,method,params}));});
 ws.onmessage=({data})=>{const message=JSON.parse(data);if(message.id){const item=pending.get(message.id);pending.delete(message.id);message.error?item.reject(message.error):item.resolve(message.result);}if(message.method==='Runtime.exceptionThrown')errors.push(message.params.exceptionDetails);};
 await call('Page.enable');await call('Runtime.enable');
 await call('Page.navigate',{url:'file:///E:/dev/WT-CDEK-Joomla-PHP-library/.webtolk/tmp/fields-audit-20261012/browser-probe.html'});
 for(let attempt=0;attempt<60;attempt++){const state=await call('Runtime.evaluate',{expression:'document.readyState==="complete" && !!window.Joomla?.Watchfield && !!window.runAudit',returnByValue:true});if(state.result.value)break;await new Promise(resolve=>setTimeout(resolve,100));}
 const response=await call('Runtime.evaluate',{expression:'runAudit()',awaitPromise:true,returnByValue:true});
 const result={data:response.result.value,exception:response.exceptionDetails,errors};
 fs.writeFileSync(__dirname+'/browser-result.json',JSON.stringify(result,null,2));console.log(JSON.stringify(result));
 const screenshot=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:true});fs.writeFileSync(__dirname+'/browser.png',Buffer.from(screenshot.data,'base64'));
 await call('Browser.close');ws.close();
})().catch(error=>{console.error(error);process.exitCode=1;});
