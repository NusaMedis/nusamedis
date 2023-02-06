//Flot Pie Chart
$(function() {

    var data = [{
        label: "BPJS",
        data: 10
    }, {
        label: "UMUM",
        data: 3
    }];

    var plotObj = $.plot($("#flot-pie-chart"), data, {
        series: {
            pie: {
                show: true,
                    radius: 1,
                    label: {
                        show: true,
                        radius: 2 / 3,
                        formatter: function (label, series) {
                            return '<div style="font-size:16pt;text-align:center;padding:2px;color:white;">' + label + '<br/>' + Math.round(series.percent) + '%</div>';

                        },
                        threshold: 0.1
                    }
            }
        },
        grid: {
            hoverable: true
        },
        legend: {
                show: false,
             },
    });

});