import{Z as y,Q as R,aF as Y,G as tt,p as et,q as at,s as rt,g as nt,c as it,b as st,_ as g,l as F,y as ot,d as lt,H as ct,M as ut,a7 as pt,k as gt}from"./mermaid.core-ikUUqBd6.js";import{p as dt}from"./chunk-4BX2VUAB-x0EXMJqq.js";import{p as ft}from"./wardley-RL74JXVD-D6mwM9qF.js";import{d as _}from"./arc-Dh05dzp2.js";import{o as ht}from"./ordinal-Cboi1Yqb.js";import"./app-DY2vj0Yd.js";import"./min-D76-RfOF.js";import"./_baseUniq-BG-1Gy2Y.js";import"./init-Gi6I4Gst.js";function mt(t,a){return a<t?-1:a>t?1:a>=t?0:NaN}function vt(t){return t}function xt(){var t=vt,a=mt,f=null,S=y(0),s=y(R),d=y(0);function o(e){var n,l=(e=Y(e)).length,c,h,v=0,u=new Array(l),i=new Array(l),x=+S.apply(this,arguments),w=Math.min(R,Math.max(-R,s.apply(this,arguments)-x)),m,D=Math.min(Math.abs(w)/l,d.apply(this,arguments)),$=D*(w<0?-1:1),p;for(n=0;n<l;++n)(p=i[u[n]=n]=+t(e[n],n,e))>0&&(v+=p);for(a!=null?u.sort(function(A,C){return a(i[A],i[C])}):f!=null&&u.sort(function(A,C){return f(e[A],e[C])}),n=0,h=v?(w-l*$)/v:0;n<l;++n,x=m)c=u[n],p=i[c],m=x+(p>0?p*h:0)+$,i[c]={data:e[c],index:n,value:p,startAngle:x,endAngle:m,padAngle:D};return i}return o.value=function(e){return arguments.length?(t=typeof e=="function"?e:y(+e),o):t},o.sortValues=function(e){return arguments.length?(a=e,f=null,o):a},o.sort=function(e){return arguments.length?(f=e,a=null,o):f},o.startAngle=function(e){return arguments.length?(S=typeof e=="function"?e:y(+e),o):S},o.endAngle=function(e){return arguments.length?(s=typeof e=="function"?e:y(+e),o):s},o.padAngle=function(e){return arguments.length?(d=typeof e=="function"?e:y(+e),o):d},o}var V=tt.pie,W={sections:new Map,showData:!1,config:V},T=W.sections,z=W.showData,yt=structuredClone(V),St=g(()=>structuredClone(yt),"getConfig"),wt=g(()=>{T=new Map,z=W.showData,ot()},"clear"),At=g(({label:t,value:a})=>{if(a<0)throw new Error(`"${t}" has invalid value: ${a}. Negative values are not allowed in pie charts. All slice values must be >= 0.`);T.has(t)||(T.set(t,a),F.debug(`added new section: ${t}, with value: ${a}`))},"addSection"),Ct=g(()=>T,"getSections"),Dt=g(t=>{z=t},"setShowData"),$t=g(()=>z,"getShowData"),U={getConfig:St,clear:wt,setDiagramTitle:et,getDiagramTitle:at,setAccTitle:rt,getAccTitle:nt,setAccDescription:it,getAccDescription:st,addSection:At,getSections:Ct,setShowData:Dt,getShowData:$t},Tt=g((t,a)=>{dt(t,a),a.setShowData(t.showData),t.sections.map(a.addSection)},"populateDb"),kt={parse:g(async t=>{const a=await ft("pie",t);F.debug(a),Tt(a,U)},"parse")},Mt=g(t=>`
  .pieCircle{
    stroke: ${t.pieStrokeColor};
    stroke-width : ${t.pieStrokeWidth};
    opacity : ${t.pieOpacity};
  }
  .pieOuterCircle{
    stroke: ${t.pieOuterStrokeColor};
    stroke-width: ${t.pieOuterStrokeWidth};
    fill: none;
  }
  .pieTitleText {
    text-anchor: middle;
    font-size: ${t.pieTitleTextSize};
    fill: ${t.pieTitleTextColor};
    font-family: ${t.fontFamily};
  }
  .slice {
    font-family: ${t.fontFamily};
    fill: ${t.pieSectionTextColor};
    font-size:${t.pieSectionTextSize};
    // fill: white;
  }
  .legend text {
    fill: ${t.pieLegendTextColor};
    font-family: ${t.fontFamily};
    font-size: ${t.pieLegendTextSize};
  }
`,"getStyles"),bt=Mt,Et=g(t=>{const a=[...t.values()].reduce((s,d)=>s+d,0),f=[...t.entries()].map(([s,d])=>({label:s,value:d})).filter(s=>s.value/a*100>=1);return xt().value(s=>s.value).sort(null)(f)},"createPieArcs"),Rt=g((t,a,f,S)=>{var P;F.debug(`rendering pie chart
`+t);const s=S.db,d=lt(),o=ct(s.getConfig(),d.pie),e=40,n=18,l=4,c=450,h=c,v=ut(a),u=v.append("g");u.attr("transform","translate("+h/2+","+c/2+")");const{themeVariables:i}=d;let[x]=pt(i.pieOuterStrokeWidth);x??(x=2);const w=o.textPosition,m=Math.min(h,c)/2-e,D=_().innerRadius(0).outerRadius(m),$=_().innerRadius(m*w).outerRadius(m*w);u.append("circle").attr("cx",0).attr("cy",0).attr("r",m+x/2).attr("class","pieOuterCircle");const p=s.getSections(),A=Et(p),C=[i.pie1,i.pie2,i.pie3,i.pie4,i.pie5,i.pie6,i.pie7,i.pie8,i.pie9,i.pie10,i.pie11,i.pie12];let k=0;p.forEach(r=>{k+=r});const G=A.filter(r=>(r.data.value/k*100).toFixed(0)!=="0"),M=ht(C).domain([...p.keys()]);u.selectAll("mySlices").data(G).enter().append("path").attr("d",D).attr("fill",r=>M(r.data.label)).attr("class","pieCircle"),u.selectAll("mySlices").data(G).enter().append("text").text(r=>(r.data.value/k*100).toFixed(0)+"%").attr("transform",r=>"translate("+$.centroid(r)+")").style("text-anchor","middle").attr("class","slice");const Z=u.append("text").text(s.getDiagramTitle()).attr("x",0).attr("y",-(c-50)/2).attr("class","pieTitleText"),L=[...p.entries()].map(([r,E])=>({label:r,value:E})),b=u.selectAll(".legend").data(L).enter().append("g").attr("class","legend").attr("transform",(r,E)=>{const I=n+l,X=I*L.length/2,J=12*n,K=E*I-X;return"translate("+J+","+K+")"});b.append("rect").attr("width",n).attr("height",n).style("fill",r=>M(r.label)).style("stroke",r=>M(r.label)),b.append("text").attr("x",n+l).attr("y",n-l).text(r=>s.getShowData()?`${r.label} [${r.value}]`:r.label);const j=Math.max(...b.selectAll("text").nodes().map(r=>(r==null?void 0:r.getBoundingClientRect().width)??0)),q=h+e+n+l+j,N=((P=Z.node())==null?void 0:P.getBoundingClientRect().width)??0,H=h/2-N/2,Q=h/2+N/2,B=Math.min(0,H),O=Math.max(q,Q)-B;v.attr("viewBox",`${B} 0 ${O} ${c}`),gt(v,c,O,o.useMaxWidth)},"draw"),Ft={draw:Rt},Vt={parser:kt,db:U,renderer:Ft,styles:bt};export{Vt as diagram};
