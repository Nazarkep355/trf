const table_body = document.querySelector('tbody')

const observer = new MutationObserver(function (mutationsList, observer) {
    for (let mutation of mutationsList) {
        if (mutation.type === 'childList') {
            let btns = document.querySelectorAll('.uab-btn');
            for (let i = 0; i < btns.length; i++) {
                // btns[i].removeEventListener('click');

                btns[i].addEventListener('click', setBtnToBanUA)
            }
        }
    }
});

const config = {childList: true, subtree: true};

// Start observing
observer.observe(table_body, config);


function setBtnToBanUA(e) {
    e.preventDefault();
    // e.stopPropagation();
    e.target.removeEventListener('click',setBtnToBanUA)
    let ip = '';
    if(e.target.tagName !== 'form'){
       ip = e.target.closest('form').getAttribute('action');
    } else {
        ip = e.target.getAttribute('action')
    }
    axios.get('/api/get_last_user_agent?ip=' + ip, {
        headers: {
            'my-sec-header': 'secure'
        }
    }).then(response => {
        const inputValue = response.data;
        let {value: user_agent} = Swal.fire({
            title: "Введіть юзерагент",
            input: "text",
            // inputLabel: "",
            inputValue,
            confirmButtonText: "Забанити",
            showCancelButton: true,
            inputValidator: (value) => {
                if (!value) {
                    return "You need to write something!";
                }
            }
        }).then((result) => {

            if (result.isConfirmed) {
                axios({
                    method: 'post',
                    url: '/api/ban_ua',
                    data: {
                        'user_agent': result.value
                    },
                    headers: {
                        'my-sec-header': 'secure'
                    }
                }).then(response => {
                    Swal.fire({
                        title: "В БАН!",
                        icon: "success",
                        draggable: true
                    });
                }).catch((error) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Помилка',
                        text: 'Не вдалося забанити юзерагент ' + error.message,
                    });
                });
            } else {
                console.log('cancelled');
                return;
            }

        });
    }).catch((e) => {
        Swal.fire({
            icon: 'error',
            title: 'Помилка',
            text: 'Не вдалося отримати останній юзерагент ' + e.message,
        });
    })


}
