<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Area;
use App\Models\Discipline;

class AreaDisciplineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            'Circo y Aéreos' => [
                'Telas (Acrobacia en Tela)', 'Trapecio Fijo', 'Trapecio Volante', 'Lira (Aro Aéreo)', 
                'Cuerda Lisa', 'Cintas (Straps)', 'Mástil Chino', 'Acrobacia de Piso', 'Acro Sport (Mano a Mano)', 
                'Malabares', 'Equilibrio (Cuerda Floja)', 'Rola Bola', 'Contorsión', 'Clown (Payaso)'
            ],
            
            'Baile y Danza' => [
                'Ballet Clásico', 'Danza Contemporánea', 'Jazz', 'Hip Hop', 'Heels (Baile en Tacones)', 
                'Salsa', 'Bachata', 'Tango', 'Reggaetón', 'Dancehall', 'Twerk', 'Flamenco', 
                'Danza Árabe', 'K-Pop', 'Tap', 'Danza Folclórica', 'Afro'
            ],
            
            'Fitness y Ejercicio' => [
                'Entrenamiento Funcional', 'CrossFit', 'HIIT', 'Calistenia', 'TRX', 
                'Levantamiento Olímpico', 'Bodybuilding / Musculación', 'Zumba', 'Fit Dance', 
                'Spinning / Ciclismo Indoor', 'Step', 'Aeróbicos'
            ],
            
            'Cuerpo y Mente' => [
                'Yoga (Hatha)', 'Yoga (Vinyasa)', 'Yoga (Ashtanga)', 'Kundalini Yoga', 'Aero Yoga',
                'Pilates (Mat)', 'Pilates (Reformer)', 'Stretching / Flexibilidad', 'Gimnasia Postural', 
                'Meditación', 'Mindfulness', 'Sonoterapia', 'Tai Chi', 'Qi Gong'
            ],
            
            'Artes Marciales y Combate' => [
                'Boxeo', 'Kickboxing', 'Muay Thai', 'Jiu Jitsu Brasileño (BJJ)', 'Judo', 
                'Karate', 'Taekwondo', 'Capoeira', 'Krav Maga', 'Esgrima', 'Lucha Libre', 'MMA'
            ],
            
            'Deportes' => [
                'Voleibol', 'Básquetbol', 'Fútbol / Futsal', 'Tenis', 'Pádel', 'Natación', 
                'Gimnasia Artística', 'Gimnasia Rítmica', 'Patinaje Artístico', 'Patinaje de Velocidad', 
                'Atletismo', 'Escalada en Muro / Boulder'
            ],
            
            'Artes Plásticas y Manualidades' => [
                'Pintura al Óleo', 'Pintura Acrílica', 'Acuarela', 'Dibujo / Ilustración', 
                'Cerámica / Alfarería', 'Escultura', 'Fotografía', 'Costura y Confección', 
                'Tejido y Bordado', 'Orfebrería / Joyería', 'Encuadernación'
            ],
            
            'Música y Artes Escénicas' => [
                'Canto (Técnica Vocal)', 'Coro', 'Guitarra', 'Piano / Teclado', 'Batería y Percusión', 
                'Violín', 'Bajo Eléctrico', 'Ukelele', 'Producción Musical', 'Actuación Teatral', 
                'Improvisación Teatral (Impro)', 'Teatro Musical', 'Locución y Doblaje'
            ],
            
            'Maternidad y Primera Infancia' => [
                'Yoga Prenatal', 'Pilates Prenatal', 'Gimnasia Postparto', 'Estimulación Temprana', 
                'Danza con Porteo', 'Gimnasia para Bebés', 'Iniciación Musical Infantil'
            ],
        ];

        DB::transaction(function () use ($categorias) {
            foreach ($categorias as $areaName => $disciplines) {
                // Creamos o buscamos el Área principal
                $area = Area::firstOrCreate([
                    'name' => $areaName
                ]);

                // Insertamos cada disciplina vinculada a su Área
                foreach ($disciplines as $disciplineName) {
                    Discipline::firstOrCreate([
                        'area_id' => $area->id,
                        'name' => $disciplineName
                    ]);
                }
            }
        });
    }
}