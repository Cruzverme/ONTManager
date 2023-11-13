function preventSpace(event) {
    if (event.keyCode === 32) {
        event.preventDefault();
    }
}

function validateNumericInput(input)
{
    input.value = input.value.replace(/\D/g, '');
}