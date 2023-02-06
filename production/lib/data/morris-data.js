$(function() {

    Morris.Bar({
        element: 'morris-bar-chart',
        data: [{
            y: 'Jan',
            a: 100,
            b: 90,
            c: 10
        }, {
            y: 'Feb',
            a: 75,
            b: 65,
            c: 20
        }, {
            y: 'Mar',
            a: 50,
            b: 40,
            c: 20
        }, {
            y: 'Apr',
            a: 75,
            b: 65,
            c: 80
        }, {
            y: 'Mei',
            a: 50,
            b: 40,
            c: 8
        }, {
            y: 'Jun',
            a: 75,
            b: 65,
            c: 69
        }, {
            y: 'Jul',
            a: 100,
            b: 90,
            c: 60
        }],
        xkey: 'y',
        ykeys: ['a', 'b', 'c',],
        labels: ['IRJ', 'IGD', 'IRNA'],
        resize: true
    });
    
});
