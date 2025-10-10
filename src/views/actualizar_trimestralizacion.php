<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Actualizar Trimestralización</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <form class="flex justify-center">
        <div class="bg-white mt-12 p-6 rounded-xl shadow-lg border border-black w-[28rem] text-center">
            <h1 class="text-[#00324D] text-xl font-semibold mb-2">Actualizar Trimestralización</h1> 

            <select name="zona" class="bg-white text-[#00324D] font-bold text-base rounded-md p-2.5 border-none shadow-lg w-full mb-4 focus:outline-none focus:ring-2 focus:ring-[#00324D]">
                <option value="">Seleccione la zona a la que pertenece la ficha</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="5">5</option>
                <option value="6">6</option>
            </select>

            <div class="flex justify-between mb-4">
                <input type="text" placeholder="Número de la ficha" class="bg-white text-[#00324D] font-bold text-base rounded-md p-2.5 border-none shadow-lg w-[10rem]">
                <input type="text" placeholder="Nombre del instructor" class="bg-white text-[#00324D] font-bold text-base rounded-md p-2.5 border-none shadow-lg w-[12rem]">
            </div>

            <select name="tipo" class="bg-white text-[#00324D] font-bold text-base rounded-md p-2.5 border-none shadow-lg w-full mb-4 focus:outline-none focus:ring-2 focus:ring-[#00324D]">
                <option value="">Tipo de instructor</option>
                <option value="Contratista">Contratista</option>
                <option value="Planta">Planta</option>
            </select>

            <div class="flex justify-center gap-8 mb-4">
                <select name="hora_inicio" class="bg-white text-[#00324D] font-bold text-base rounded-md p-2.5 border-none shadow-lg w-[12rem]">
                    <option value="">Hora de inicio</option>
                    <?php
                        for ($i = 6; $i <= 22; $i++) {
                            echo "<option value='$i:00'>$i:00</option>";
                        }
                    ?>
                </select>

                <select name="hora_fin" class="bg-white text-[#00324D] font-bold text-base rounded-md p-2.5 border-none shadow-lg w-[12rem]">
                    <option value="">Hora de fin</option>
                    <?php
                        for ($i = 6; $i <= 22; $i++) {
                            echo "<option value='$i:00'>$i:00</option>";
                        }
                    ?>
                </select>
            </div>

            <div class="mb-4">
                <textarea id="descripcion" name="descripcion" placeholder="Diligencie la competencia aquí"
                    class="bg-white text-[#00324D] font-bold text-base rounded-md p-10 border-none shadow-lg w-[24.6rem] focus:outline-none focus:ring-2 focus:ring-[#00324D]"></textarea>
            </div>

            <button type="submit" class="bg-[#00324D] text-white font-bold text-base py-3 rounded-md w-full mt-4 shadow-lg hover:bg-[#004a6f] transition">
                CREAR TRIMESTRALIZACIÓN
            </button>
        </div>
    </form>
</body>
</html>
//borrar despues del push