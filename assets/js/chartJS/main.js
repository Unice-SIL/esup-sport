import { Chart, registerables } from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';
// import { getRelativePosition } from 'chart.js/helpers';
Chart.register(...registerables);
Chart.register(ChartDataLabels);

global._chart = {};

_chart.colors = [
    "#B892FF",
    "#F1948A",
    "#C39BD3",
    "#BB8FCE",
    "#7FB3D5",
    "#85C1E9",
    "#76D7C4",
    "#73C6B6",
    "#7DCEA0",
    "#82E0AA",
    "#F7DC6F",
    "#F8C471",
    "#F0B27A",
    "#E59866",
    "#C0392B",
    "#E74C3C",
    "#9B59B6",
    "#8E44AD",
    "#2980B9",
    "#3498DB",
    "#1ABC9C",
    "#16A085",
    "#27AE60",
    "#2ECC71",
    "#F1C40F",
    "#F39C12",
    "#E67E22",
    "#D35400",
    "#641E16",
    "#78281F",
    "#512E5F",
    "#4A235A",
    "#154360",
    "#21618C",
    "#0E6251",
    "#0B5345",
    "#145A32",
    "#186A3B",
    "#7D6608",
    "#7E5109",
    "#784212",
    "#6E2C00",
];

_chart.getNbColors = function() {
    let size = 0;
    for (let color in _chart.colors) {
        size++;
    }
    return size;
}

_chart.nbColors = _chart.getNbColors();

_chart.usedColors = [];

_chart.getNbUsedColors = function() {
    let size = 0;
    for (let color in _chart.usedColors) {
        size++;
    }
    return size;
}

_chart.nbUsedColors = _chart.getNbUsedColors();

_chart.createChart = function(data) {
    let option = data[0];
    let recherche = data[1];
    let value = data[2];
    let text = data[3];
    let idCanvas = "canvasPersonnalise"

    switch (option) {
        case 'Flux d\'inscription':
        case 'Registration flow':
            this.createLineChart(option, text, value, idCanvas);
            break;
        case 'Fréquentation du cours':
        case 'Course attendance':
            this.createLineChart(option, text, value, idCanvas);
            break;
        case 'Nombre d\'achat de cartes':
        case 'Number of card purchases':
            this.createLineChart(option, text, value, idCanvas);
            break;
        case 'Nombre étudiant/personnel':
        case 'Number of student/staff':
            this.createVertictalBarChart(option, value, idCanvas);
            break;
        default:
            break;
    }
}

/* Creation d'éléments */

_chart.createDiv = function(id, className) {
    var div = document.createElement('div');
    div.className = className;
    div.setAttribute('id', id);
    return div;
};

_chart.createBouton = function(color, icone, label) {
    var bouton = document.createElement('a');
    bouton.className = 'btn btn-' + color;
    var iconeBouton = document.createElement('spsn');
    iconeBouton.className = "fas fa-" + icone;
    bouton.setAttribute('aria-label', label);
    bouton.style.color = 'white';
    bouton.appendChild(iconeBouton);

    return bouton;
};

_chart.createPieChart = function(title, datas, position, idCanvas, legendPadding = 0, displayLegend = true) {
    let dataValues = [];
    let dataColors = [];
    let dataLabels = [];

    datas.forEach(function(item) {
        if (item instanceof Object) {
            dataValues.push(item['nbr']);
            dataLabels.push(item['libelle']);
            dataColors.push(_chart.getColor());
        }
    });

    let config = {
        type: 'pie',
        data: {
            datasets: [{
                data: dataValues,
                backgroundColor: dataColors,
            }],
            labels: dataLabels,
        },
        options: {
            legend: {
                display: displayLegend,
                position: position,
                labels: {
                    fontSize: 10,
                },
                padding: legendPadding
            },
            responsive: true,
            title: {
                display: true,
                text: title,
            },
            // Parametrage du tooltip sur le graphique
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var dataset = data.datasets[tooltipItem.datasetIndex];
                        var meta = dataset._meta[Object.keys(dataset._meta)[0]];
                        var total = meta.total;
                        var currentValue = dataset.data[tooltipItem.index];
                        var percentage = parseFloat((currentValue / total * 100).toFixed(1));
                        return data.labels[tooltipItem.index] + ": " + currentValue + ' (' + percentage + '%)';
                    },
                }
            },
            // Parametrage de plugins
	        plugins: {
		        // Parametrage des labels contenu dans le graphique
				datalabels: {
                    display: false
				}
			}
        }
    };

    let canvas = document.getElementById(idCanvas);
    var ctx = document.getElementById(idCanvas).getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    window.myLine = new Chart(ctx, config);

    _chart.removeLoader(idCanvas);
    _chart.usedColors = [];
};

_chart.createVertictalBarChart = function(title, datas, idCanvas, labels=null) {
    let datasets = [];
    let yAxesType = 'linear';

    datas.forEach(function(item) {
        for (const [key, value] of Object.entries(item)) {
            if (value > 100) {
                // Echelle scientifique
                yAxesType = 'linear';
            }
            let color = _chart.getColor();
            let values = [value];
            if(Array.isArray(value)){
                values = value;
            }
            datasets.push({
                label: key,
                backgroundColor: color,
                borderColor: color,
                borderWidth: 1,
                data: values,
                datalabels: {
                    align: 'end',
                    anchor: 'end'
                }
            });
        }
    });

    var barChartData = {
        labels: labels != null && labels.length >= 1 ? labels : [''],
        datasets: datasets,
    };

    let configLegend = {
        position: 'bottom',
        labels: {
            fontSize: 10,
        },
        padding: 0
    };
    if(labels.length != null){
        configLegend = {
            display: false
        };
    }

    let config = {
        type: 'bar',
        data: barChartData,
        // plugins: [ChartDataLabels],
        options: {
            responsive: true,
            legend: configLegend,
            title: {
                display: true,
                text: title,
            },
            scales: {
                x: {
                    display: true,
                },
                y: {
                    display: true,
                    type: yAxesType
                }
            },
            // Parametrage du tooltip sur le graphique
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var total = 0;
                        data.datasets.forEach(function(dataset) {
                            total += dataset.data[tooltipItem.index];
                        });
                        var dataset = data.datasets[tooltipItem.datasetIndex];
                        var currentValue = dataset.data[tooltipItem.index];
                        var percentage = parseFloat((currentValue / total * 100).toFixed(1));
                        return data.labels[tooltipItem.index] + ": " + currentValue + ' (' + percentage + '%)';
                    },
                }
            },
            // Parametrage de plugins
	        plugins: {
		        // Parametrage des labels contenu dans le graphique
				datalabels: {
                    // Gestion de la couleur du label
                    color: function(context) {
						return '#1a1a1a';
					},
					// Gestion du contenu du label
					formatter: function(value, context) {
                        return value;
                    },
                    // Gestion de la police
                    font: {
						weight: 'bold'
					},
                    offset: function(context) {
                        var max = context.chart.scales["y"].max;
                        var index = context.dataIndex;
                        var value = context.dataset.data[index];
                        var percent = value*100/max;
                        if(percent >= 95){
                            return -30;
                        }
						return 0;
					},
				}
			}
        }
    };

    let canvas = document.getElementById(idCanvas);
    var ctx = document.getElementById(idCanvas).getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    window.myLine = new Chart(ctx, config);

    _chart.removeLoader(idCanvas);
    _chart.usedColors = [];
};

_chart.createVertictalBarGroupChart = function(title, datas, idCanvas) {
    let datasetsWithKey = [];
    let datasets = [];
    let labels = [];
    let yAxesType = 'linear';

    var donnees = datas[0];
    var size = datas[1];


    var nombreTotal = 0;
    var index = 0;
    for (const [group, item] of Object.entries(donnees)) {
        labels.push(group);
        for (const [key, value] of Object.entries(item)) {
            if (!(key in datasetsWithKey)) {
                let color = _chart.getColor();

                var defaultData = [];
                for (var i = 0; i < size; i++) {
                    defaultData[i] = 0;
                }

                datasetsWithKey[key] = {
                    label: key,
                    backgroundColor: color,
                    borderColor: color,
                    borderWidth: 1,
                    data: defaultData,
                    datalabels: {
                        align: 'end',
                        anchor: 'end'
                    }
                };
            }
            datasetsWithKey[key].data[index] = value;
            nombreTotal += value;
        }
        index++;
    };


    for (const [key, element] of Object.entries(datasetsWithKey)) {
        datasets.push(element);
    }


    var barChartData = {
        labels: labels,
        datasets: datasets,
    };

    let config = {
        type: 'bar',
        data: barChartData,
        options: {
            nbreTotal: nombreTotal,
            responsive: true,
            legend: {
                position: 'bottom',
                labels: {
                    fontSize: 10,
                },
                padding: 0
            },
            maintainAspectRatio: false,
            title: {
                display: true,
                text: title,
            },
            scales: {
                x: {
                    display: true,
                },
                y: {
                    display: true,
                    type: yAxesType
                }
            },
            // Parametrage du tooltip sur le graphique
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var total = this._chart.options.nbreTotal;
                        var dataset = data.datasets[tooltipItem.datasetIndex];
                        var currentValue = dataset.data[tooltipItem.index];
                        var percentage = parseFloat((currentValue / total * 100).toFixed(1));
                        return dataset.label + ": " + currentValue + ' (' + percentage + '%)';
                    },
                }
            },
            // Parametrage de plugins
	        plugins: {
		        // Parametrage des labels contenu dans le graphique
				datalabels: {
                    // Gestion de la couleur du label
                    color: function(context) {
						return '#1a1a1a';
					},
					// Gestion du contenu du label
					formatter: function(value, context) {
                        return value;
                    },
                    // Gestion de l'affichage du label
					display: function(context) {
						// Si la donnee est nulle, alors elle est masquee
						return context.chart.isDatasetVisible(context.datasetIndex);
					},
                    offset: function(context) {
                        var max = context.chart.scales["y"].max;
                        var index = context.dataIndex;
                        var value = context.dataset.data[index];
                        var percent = value*100/max;
                        if(percent >= 85){
                            return -30;
                        }
						return 0;
					},
                    // Gestion de la police
                    font: {
						weight: 'bold',
                        // size: 14
					},
                    rotation: -90,
				}
			},
        }
    };

    let canvas = document.getElementById(idCanvas);
    var ctx = document.getElementById(idCanvas).getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    window.myLine = new Chart(ctx, config);

    _chart.removeLoader(idCanvas);
    _chart.usedColors = [];
};

_chart.createPopulationPyramidChart = function(title, datas, idCanvas) {
    let datasets = { labels: new Array(), datasets: new Array() };
    let color = _chart.getColor();
    let cpt;
    let homme = {
        label: "",
        barPercentage: 0.5,
        categoryPercentage: 1.0,
        data: new Array(),
        backgroundColor: color,
        hoverBackgroundColor: color
    };
    color = _chart.getColor();
    let femme = {
        label: "",
        barPercentage: 0.5,
        categoryPercentage: 1.0,
        data: new Array(),
        backgroundColor: color,
        hoverBackgroundColor: color
    };
    let total = 0;
    for (const [tranche, donnees] of Object.entries(datas)) {
        datasets.labels.push(tranche);
        cpt = 0;
        for (const [sexe, nombre] of Object.entries(donnees)) {
            total += nombre;
            if (cpt != 0) {
                femme.label = sexe;
                femme.data.push(-nombre);
            } else {
                homme.label = sexe;
                homme.data.push(nombre);
            }
            cpt++;
        }
    }
    femme.total = total;
    homme.total = total;

    datasets.datasets.push(homme);
    datasets.datasets.push(femme);

    let options = {
        indexAxis: 'y',
        tooltips: {
            enabled: true,
            callbacks: {
                label: function(tooltipItems, data) {
                    var currentValue = Math.abs(tooltipItems.xLabel);
                    var total = data.datasets[tooltipItems.datasetIndex].total;
                    var percentage = parseFloat((currentValue / total * 100).toFixed(1));
                    return data.datasets[tooltipItems.datasetIndex].label + ": " + currentValue + " (" + percentage + "%)";
                }
            }
        },
        title: {
            display: true,
            text: title
        },
        hover: { animationDuration: 0 },
        scales: {
            x: {
                ticks: {
                    beginAtZero: true,
                    fontFamily: "'Apex New Book', sans-serif",
                    fontSize: 11,
                    callback: function(value, index, values) {
                        return Math.abs(value);
                    }
                },
                scaleLabel: { display: false },
                stacked: true
            },
            y: {
                gridLines: {
                    display: false,
                    color: "#fff",
                    zeroLineColor: "#fff",
                    zeroLineWidth: 0
                },
                ticks: {
                    fontFamily: "'Apex New Book', sans-serif",
                    fontSize: 11
                },
                stacked: true
            }
        },
        legend: {
            position: 'bottom',
            labels: {
                fontSize: 10,
            },
            padding: 0
        },

        animation: {
            onComplete: function() {
                var ctx = this.$context;
                var chartInstance = ctx.chart;
                ctx.textAlign = "left";
                ctx.font = "0px Open Sans";
                ctx.fillStyle = "#fff";

                // Chart.helpers.each(this.data.datasets.forEach(function(dataset, i) {
                //     var meta = chartInstance.controller.getDatasetMeta(i);
                //     Chart.helpers.each(meta.data.forEach(function(bar, index) {
                //         data = dataset.data[index];
                //         if (i == 0) {
                //             ctx.fillText(data, 50, bar._model.y + 4);
                //         } else {
                //             ctx.fillText(data, bar._model.x - 25, bar._model.y + 4);
                //         }
                //     }), this)
                // }), this);
            }
        },
        pointLabelFontFamily: "Quadon Extra Bold",
        scaleFontFamily: "Quadon Extra Bold",
        // Parametrage de plugins
        plugins: {
            // Parametrage des labels contenu dans le graphique
            datalabels: {
                display: false
            }
        },
    };

    var ctx = document.getElementById(idCanvas);
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: datasets,
        options: options,
    });
    //window.myLine = new Chart(ctx, config);

    _chart.removeLoader(idCanvas);
    _chart.usedColors = [];
};

_chart.createLineChart = function(title, label, datas, idCanvas) {
    let dataLabels = [];
    let dataValues = [];
    // let color = '#'+Math.floor(Math.random()*16777215).toString(16);
    let color = _chart.getColor();

    datas.forEach(function(item) {
        if (item instanceof Object) {
            for (const [key, value] of Object.entries(item)) {
                dataValues.push(value);
                dataLabels.push(key);
            }
        } else {

        }
    });

    var config = {
        type: 'line',
        data: {
            labels: dataLabels,
            datasets: [{
                label: label,
                backgroundColor: color,
                borderColor: color,
                data: dataValues,
                fill: false,
            }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: title
            },
            tooltips: {
                mode: 'index',
                intersect: false,
            },
            hover: {
                mode: 'nearest',
                intersect: true
            },
            scales: {
                x: {
                    display: true,
                },
                y: {
                    display: true,
                }
            },
            // Parametrage de plugins
	        plugins: {
		        // Parametrage des labels contenu dans le graphique
				datalabels: {
                    display: false
				}
			},
        }
    };

    let canvas = document.getElementById(idCanvas);
    var ctx = document.getElementById(idCanvas).getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    window.myLine = new Chart(ctx, config);

    _chart.removeLoader(idCanvas);
    _chart.usedColors = [];
}

_chart.getRndInteger = function() {
    return Math.floor(Math.random() * (_chart.nbColors - 0)) + 0;
}

_chart.getColor = function() {
    // color = _chart.getRndInteger();
    // if (_chart.nbUsedColors < _chart.nbColors) {
    //     while (_chart.usedColors.includes(color)) {
    //         color = _chart.getRndInteger();
    //     }
    //     _chart.usedColors.push(color);
    // } else {
    //     _chart.usedColors = [];
    //     _chart.usedColors.push(color);
    // }

    // return _chart.colors[color];
    return '#' + Math.floor(Math.random() * 16777215).toString(16);
}

_chart.removeLoader = function(idCanvas) {
    let idLoader = idCanvas + "Loader";
    document.getElementById(idLoader).remove();
}