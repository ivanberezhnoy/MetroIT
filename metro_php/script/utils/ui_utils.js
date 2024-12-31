export function addTableRow(table, rowIndex)
{
    var row = table.insertRow(rowIndex);
    row.style = "border: 1px solid black;border-collapse: collapse;";

    return row;
}

export function addRowCell(row, cellIndex)
{
    var cell = row.insertCell(cellIndex);
    cell.style = "border: 1px solid black;border-collapse: collapse;";

    return cell;
}