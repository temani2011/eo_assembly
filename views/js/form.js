/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

const $ = window.$

$(() => {
    setCalculatedAssemblyTime()
    createFeatureList()
})

const setCalculatedAssemblyTime = () => {
    let customAssemplyElement = document.getElementById('form_assembly_assembly_time_custom')
    let calculatedAssemplyElement = document.getElementById('form_assembly_calculated_assembly_time')

    if ('undefiend' == calculatedAssemplyElement || null == calculatedAssemplyElement) return void 0

    calculatedAssemplyElement.dataset.basevalue = calculatedAssemplyElement.value
    if (customAssemplyElement.value > 0) calculatedAssemplyElement.value = customAssemplyElement.value

    $(document).on('change', '#form_assembly_assembly_time_custom', event => {
        calculatedAssemplyElement.value = parseInt(event.target.value)
            ? parseInt(event.target.value)
            : calculatedAssemplyElement.dataset.basevalue
    })
}

const createFeatureList = () => {
    const featureBlock = document.getElementById('form_assembly_features')

    if (!featureBlock) return void 0

    const nameInput = featureBlock.querySelectorAll('[name$="[name]"]')

    const listWrapper = document.createElement('div')

    nameInput.forEach(element => {
        const id = element.name.match(/\[(\d+)\]/i).pop()

        if (id) {
            const listItem = createFeatureItem(element, id)
            listWrapper.appendChild(listItem)
        }
    })

    featureBlock.parentElement.appendChild(listWrapper)
}

const createFeatureItem = (element, id) => {
    const link = document.createElement('a')
    link.dataset.toggle = 'collapse'
    link.href = '#collapse-features-' + id
    link.innerHTML = element.value

    const card = document.createElement('div')
    card.classList.add('card')

    const header = document.createElement('div')
    header.classList.add('card-header')

    const collapse = document.createElement('div')
    collapse.classList.add('collapse')
    collapse.id = 'collapse-features-' + id

    const body = document.createElement('div')
    body.classList.add('card-body')

    const featureItem = createFeatureValuesList(id)

    body.appendChild(featureItem)
    collapse.appendChild(body)
    header.appendChild(link)
    card.appendChild(header)
    card.appendChild(collapse)

    return card
}

const createFeatureValuesList = (id) => {
    const featureValuesBlock = document.getElementById(`form_assembly_features_${id}_values`)
    const nameInput = featureValuesBlock.querySelectorAll('[name$="[value]"]')

    const list = document.createElement('div')

    nameInput.forEach(element => {
        const valueId = element.name.match(/\[values\]\[(\d+)\]/i).pop()

        if (valueId) {
            const listItem = createFeatureValueItem(element, id, valueId)
            list.appendChild(listItem)
        }
    })

    return list
}

const createFeatureValueItem = (element, id, valueId) => {
    const inputElement = document.getElementById(`form_assembly_features_${id}_values_${valueId}_assembly_time`)

    const formRow = document.createElement('div')
    formRow.className = 'form-group row'

    const label = document.createElement('label')
    label.className = 'd-flex text-right justify-content-end align-items-center col-sm-6 mb-0'
    label.innerHTML = element.value

    const col = document.createElement('div')
    col.className = 'col-sm-6'

    const input = document.createElement('input')
    input.className = 'form-control'
    input.type = 'number'
    input.value = inputElement.value
    input.addEventListener('change', event => changeFormFeatureValue(event, id, valueId))

    col.appendChild(input)
    formRow.appendChild(label)
    formRow.appendChild(col)

    return formRow
}

const changeFormFeatureValue = (event, id, valueId) => {
    const formInput = document.getElementById(`form_assembly_features_${id}_values_${valueId}_assembly_time`)
    formInput.value = event.target.value
}
