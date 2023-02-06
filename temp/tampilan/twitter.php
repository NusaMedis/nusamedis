<script charset="utf-8" src="http://widgets.twimg.com/j/2/widget.js"></script>
<script>
new TWTR.Widget({
version: 2,
type: 'profile',
rpp: 10,
interval: 30000,
width: 279,
height: 210,
theme: {
shell: {
background: '#990000',
color: '#ffffff'
},
tweets: {
background: '#f7f7f7',
color: '#000000',
links: '#0a7a00'
}
},
features: {
scrollbar: true,
loop: false,
live: true,
behavior: 'all'
}
}).render().setUser('SIKITA_Dental').start();
</script>