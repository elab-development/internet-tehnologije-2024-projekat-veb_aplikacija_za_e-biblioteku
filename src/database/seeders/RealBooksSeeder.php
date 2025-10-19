<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class RealBooksSeeder extends Seeder
{
    public function run(): void
    {
        $books = [
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'year' => 1949,
                'genre' => 'Dystopian Fiction',
                'isbn' => '9780451524935',
                'description' => 'A dystopian social science fiction novel and cautionary tale about the dangers of totalitarianism.'
            ],
            [
                'title' => 'To Kill a Mockingbird',
                'author' => 'Harper Lee',
                'year' => 1960,
                'genre' => 'Fiction',
                'isbn' => '9780061120084',
                'description' => 'The story of young Scout Finch, her brother Jem, and their father Atticus in the American South during the 1930s.'
            ],
            [
                'title' => 'The Great Gatsby',
                'author' => 'F. Scott Fitzgerald',
                'year' => 1925,
                'genre' => 'Fiction',
                'isbn' => '9780743273565',
                'description' => 'A story of the fabulously wealthy Jay Gatsby and his love for the beautiful Daisy Buchanan.'
            ],
            [
                'title' => 'Pride and Prejudice',
                'author' => 'Jane Austen',
                'year' => 1813,
                'genre' => 'Romance',
                'isbn' => '9780141439518',
                'description' => 'A romantic novel that follows the character development of Elizabeth Bennet.'
            ],
            [
                'title' => 'The Catcher in the Rye',
                'author' => 'J.D. Salinger',
                'year' => 1951,
                'genre' => 'Fiction',
                'isbn' => '9780316769174',
                'description' => 'A story about teenage rebellion and alienation in 1950s America.'
            ],
            [
                'title' => 'Lord of the Flies',
                'author' => 'William Golding',
                'year' => 1954,
                'genre' => 'Fiction',
                'isbn' => '9780571056866',
                'description' => 'A story about a group of British boys stranded on an uninhabited island.'
            ],
            [
                'title' => 'The Hobbit',
                'author' => 'J.R.R. Tolkien',
                'year' => 1937,
                'genre' => 'Fantasy',
                'isbn' => '9780547928227',
                'description' => 'A fantasy novel about the adventures of Bilbo Baggins, a hobbit.'
            ],
            [
                'title' => 'Harry Potter and the Philosopher\'s Stone',
                'author' => 'J.K. Rowling',
                'year' => 1997,
                'genre' => 'Fantasy',
                'isbn' => '9780747532699',
                'description' => 'The first novel in the Harry Potter series about a young wizard.'
            ],
            [
                'title' => 'The Chronicles of Narnia: The Lion, the Witch and the Wardrobe',
                'author' => 'C.S. Lewis',
                'year' => 1950,
                'genre' => 'Fantasy',
                'isbn' => '9780064471046',
                'description' => 'A fantasy novel about four children who discover the magical world of Narnia.'
            ],
            [
                'title' => 'The Da Vinci Code',
                'author' => 'Dan Brown',
                'year' => 2003,
                'genre' => 'Mystery',
                'isbn' => '9780307474278',
                'description' => 'A mystery thriller novel about a murder in the Louvre Museum.'
            ],
            [
                'title' => 'The Alchemist',
                'author' => 'Paulo Coelho',
                'year' => 1988,
                'genre' => 'Philosophy',
                'isbn' => '9780061122415',
                'description' => 'A philosophical novel about a young Andalusian shepherd who travels to Egypt.'
            ],
            [
                'title' => 'The Kite Runner',
                'author' => 'Khaled Hosseini',
                'year' => 2003,
                'genre' => 'Fiction',
                'isbn' => '9781594631931',
                'description' => 'A story about friendship, betrayal, and redemption set in Afghanistan.'
            ],
            [
                'title' => 'Life of Pi',
                'author' => 'Yann Martel',
                'year' => 2001,
                'genre' => 'Fiction',
                'isbn' => '9780156027328',
                'description' => 'A fantasy adventure novel about a young man stranded on a lifeboat with a Bengal tiger.'
            ],
            [
                'title' => 'The Book Thief',
                'author' => 'Markus Zusak',
                'year' => 2005,
                'genre' => 'Historical Fiction',
                'isbn' => '9780375831003',
                'description' => 'A historical novel set in Nazi Germany, narrated by Death.'
            ],
            [
                'title' => 'The Fault in Our Stars',
                'author' => 'John Green',
                'year' => 2012,
                'genre' => 'Young Adult',
                'isbn' => '9780525478812',
                'description' => 'A young adult novel about two teenagers who meet in a cancer support group.'
            ],
            [
                'title' => 'Gone Girl',
                'author' => 'Gillian Flynn',
                'year' => 2012,
                'genre' => 'Thriller',
                'isbn' => '9780307588364',
                'description' => 'A psychological thriller about the disappearance of Amy Dunne.'
            ],
            [
                'title' => 'The Hunger Games',
                'author' => 'Suzanne Collins',
                'year' => 2008,
                'genre' => 'Science Fiction',
                'isbn' => '9780439023481',
                'description' => 'A dystopian novel about a televised fight to the death.'
            ],
            [
                'title' => 'The Girl with the Dragon Tattoo',
                'author' => 'Stieg Larsson',
                'year' => 2005,
                'genre' => 'Crime',
                'isbn' => '9780307269751',
                'description' => 'A crime thriller about a journalist and a computer hacker investigating a disappearance.'
            ],
            [
                'title' => 'The Help',
                'author' => 'Kathryn Stockett',
                'year' => 2009,
                'genre' => 'Historical Fiction',
                'isbn' => '9780399155345',
                'description' => 'A story about African American maids working in white households in 1960s Mississippi.'
            ],
            [
                'title' => 'The Road',
                'author' => 'Cormac McCarthy',
                'year' => 2006,
                'genre' => 'Post-Apocalyptic',
                'isbn' => '9780307265432',
                'description' => 'A post-apocalyptic novel about a father and son traveling through a devastated landscape.'
            ],
            [
                'title' => 'Dune',
                'author' => 'Frank Herbert',
                'year' => 1965,
                'genre' => 'Science Fiction',
                'isbn' => '9780441172719',
                'description' => 'A science fiction epic set on the desert planet Arrakis.'
            ],
            [
                'title' => 'Foundation',
                'author' => 'Isaac Asimov',
                'year' => 1951,
                'genre' => 'Science Fiction',
                'isbn' => '9780553293357',
                'description' => 'A science fiction novel about the fall and rise of galactic civilizations.'
            ],
            [
                'title' => 'The Handmaid\'s Tale',
                'author' => 'Margaret Atwood',
                'year' => 1985,
                'genre' => 'Dystopian Fiction',
                'isbn' => '9780385490818',
                'description' => 'A dystopian novel set in a totalitarian society where women are subjugated.'
            ],
            [
                'title' => 'Beloved',
                'author' => 'Toni Morrison',
                'year' => 1987,
                'genre' => 'Fiction',
                'isbn' => '9781400033409',
                'description' => 'A novel about a former slave haunted by the ghost of her dead child.'
            ],
            [
                'title' => 'The Color Purple',
                'author' => 'Alice Walker',
                'year' => 1982,
                'genre' => 'Fiction',
                'isbn' => '9780151191536',
                'description' => 'An epistolary novel about the life of African American women in the early 20th century.'
            ],
            [
                'title' => 'One Hundred Years of Solitude',
                'author' => 'Gabriel García Márquez',
                'year' => 1967,
                'genre' => 'Magical Realism',
                'isbn' => '9780060883287',
                'description' => 'A multi-generational story about the Buendía family in the fictional town of Macondo.'
            ],
            [
                'title' => 'The Old Man and the Sea',
                'author' => 'Ernest Hemingway',
                'year' => 1952,
                'genre' => 'Fiction',
                'isbn' => '9780684801223',
                'description' => 'A short novel about an old Cuban fisherman and his struggle with a giant marlin.'
            ],
            [
                'title' => 'Catch-22',
                'author' => 'Joseph Heller',
                'year' => 1961,
                'genre' => 'Satire',
                'isbn' => '9780684833392',
                'description' => 'A satirical novel about the absurdity of war and military bureaucracy.'
            ],
            [
                'title' => 'Slaughterhouse-Five',
                'author' => 'Kurt Vonnegut',
                'year' => 1969,
                'genre' => 'Science Fiction',
                'isbn' => '9780385333849',
                'description' => 'An anti-war novel about Billy Pilgrim, a soldier who becomes unstuck in time.'
            ],
            [
                'title' => 'The Grapes of Wrath',
                'author' => 'John Steinbeck',
                'year' => 1939,
                'genre' => 'Fiction',
                'isbn' => '9780143039433',
                'description' => 'A novel about the Joad family\'s migration from Oklahoma to California during the Great Depression.'
            ],
            [
                'title' => 'East of Eden',
                'author' => 'John Steinbeck',
                'year' => 1952,
                'genre' => 'Fiction',
                'isbn' => '9780140186390',
                'description' => 'A novel about two families in California\'s Salinas Valley.'
            ],
            [
                'title' => 'The Sun Also Rises',
                'author' => 'Ernest Hemingway',
                'year' => 1926,
                'genre' => 'Fiction',
                'isbn' => '9780743297332',
                'description' => 'A novel about a group of expatriates traveling from Paris to Pamplona for the bullfights.'
            ],
            [
                'title' => 'A Farewell to Arms',
                'author' => 'Ernest Hemingway',
                'year' => 1929,
                'genre' => 'Fiction',
                'isbn' => '9780684801469',
                'description' => 'A novel about an American ambulance driver in Italy during World War I.'
            ],
            [
                'title' => 'The Sound and the Fury',
                'author' => 'William Faulkner',
                'year' => 1929,
                'genre' => 'Fiction',
                'isbn' => '9780679732242',
                'description' => 'A novel about the decline of the Compson family in Mississippi.'
            ],
            [
                'title' => 'As I Lay Dying',
                'author' => 'William Faulkner',
                'year' => 1930,
                'genre' => 'Fiction',
                'isbn' => '9780679732259',
                'description' => 'A novel about the Bundren family\'s journey to bury their matriarch.'
            ],
            [
                'title' => 'Light in August',
                'author' => 'William Faulkner',
                'year' => 1932,
                'genre' => 'Fiction',
                'isbn' => '9780679732266',
                'description' => 'A novel about race, identity, and social issues in the American South.'
            ],
            [
                'title' => 'Absalom, Absalom!',
                'author' => 'William Faulkner',
                'year' => 1936,
                'genre' => 'Fiction',
                'isbn' => '9780679732181',
                'description' => 'A novel about the rise and fall of Thomas Sutpen and his family.'
            ],
            [
                'title' => 'Go Tell It on the Mountain',
                'author' => 'James Baldwin',
                'year' => 1953,
                'genre' => 'Fiction',
                'isbn' => '9780385334204',
                'description' => 'A semi-autobiographical novel about a young man\'s coming of age in Harlem.'
            ],
            [
                'title' => 'Another Country',
                'author' => 'James Baldwin',
                'year' => 1962,
                'genre' => 'Fiction',
                'isbn' => '9780679744719',
                'description' => 'A novel about race, sexuality, and identity in 1950s New York.'
            ],
            [
                'title' => 'Giovanni\'s Room',
                'author' => 'James Baldwin',
                'year' => 1956,
                'genre' => 'Fiction',
                'isbn' => '9780345806567',
                'description' => 'A novel about a young American man living in Paris and his relationships.'
            ],
            [
                'title' => 'If Beale Street Could Talk',
                'author' => 'James Baldwin',
                'year' => 1974,
                'genre' => 'Fiction',
                'isbn' => '9780307275936',
                'description' => 'A novel about a young couple\'s struggle against injustice and racism.'
            ],
            [
                'title' => 'The Fire Next Time',
                'author' => 'James Baldwin',
                'year' => 1963,
                'genre' => 'Non-fiction',
                'isbn' => '9780679744726',
                'description' => 'A collection of essays about race relations in America.'
            ],
            [
                'title' => 'Notes of a Native Son',
                'author' => 'James Baldwin',
                'year' => 1955,
                'genre' => 'Non-fiction',
                'isbn' => '9780807006238',
                'description' => 'A collection of essays about being black in America.'
            ],
            [
                'title' => 'The Autobiography of Malcolm X',
                'author' => 'Malcolm X',
                'year' => 1965,
                'genre' => 'Autobiography',
                'isbn' => '9780345350688',
                'description' => 'The autobiography of civil rights activist Malcolm X.'
            ],
            [
                'title' => 'I Know Why the Caged Bird Sings',
                'author' => 'Maya Angelou',
                'year' => 1969,
                'genre' => 'Autobiography',
                'isbn' => '9780345514400',
                'description' => 'The first volume of Maya Angelou\'s autobiography.'
            ],
            [
                'title' => 'Gather Together in My Name',
                'author' => 'Maya Angelou',
                'year' => 1974,
                'genre' => 'Autobiography',
                'isbn' => '9780345514417',
                'description' => 'The second volume of Maya Angelou\'s autobiography.'
            ],
            [
                'title' => 'Singin\' and Swingin\' and Gettin\' Merry Like Christmas',
                'author' => 'Maya Angelou',
                'year' => 1976,
                'genre' => 'Autobiography',
                'isbn' => '9780345514424',
                'description' => 'The third volume of Maya Angelou\'s autobiography.'
            ],
            [
                'title' => 'The Heart of a Woman',
                'author' => 'Maya Angelou',
                'year' => 1981,
                'genre' => 'Autobiography',
                'isbn' => '9780345514431',
                'description' => 'The fourth volume of Maya Angelou\'s autobiography.'
            ],
            [
                'title' => 'All God\'s Children Need Traveling Shoes',
                'author' => 'Maya Angelou',
                'year' => 1986,
                'genre' => 'Autobiography',
                'isbn' => '9780345514448',
                'description' => 'The fifth volume of Maya Angelou\'s autobiography.'
            ],
            [
                'title' => 'A Song Flung Up to Heaven',
                'author' => 'Maya Angelou',
                'year' => 2002,
                'genre' => 'Autobiography',
                'isbn' => '9780345514455',
                'description' => 'The sixth volume of Maya Angelou\'s autobiography.'
            ],
            [
                'title' => 'Mom & Me & Mom',
                'author' => 'Maya Angelou',
                'year' => 2013,
                'genre' => 'Autobiography',
                'isbn' => '9781400066117',
                'description' => 'The seventh and final volume of Maya Angelou\'s autobiography.'
            ],
            [
                'title' => 'The Bluest Eye',
                'author' => 'Toni Morrison',
                'year' => 1970,
                'genre' => 'Fiction',
                'isbn' => '9780307278449',
                'description' => 'A novel about a young African American girl who longs for blue eyes.'
            ],
            [
                'title' => 'Sula',
                'author' => 'Toni Morrison',
                'year' => 1973,
                'genre' => 'Fiction',
                'isbn' => '9781400033430',
                'description' => 'A novel about the friendship between two African American women.'
            ],
            [
                'title' => 'Song of Solomon',
                'author' => 'Toni Morrison',
                'year' => 1977,
                'genre' => 'Fiction',
                'isbn' => '9781400033423',
                'description' => 'A novel about a young African American man\'s search for identity.'
            ],
            [
                'title' => 'Tar Baby',
                'author' => 'Toni Morrison',
                'year' => 1981,
                'genre' => 'Fiction',
                'isbn' => '9781400033447',
                'description' => 'A novel about race, class, and relationships in the Caribbean.'
            ],
            [
                'title' => 'Jazz',
                'author' => 'Toni Morrison',
                'year' => 1992,
                'genre' => 'Fiction',
                'isbn' => '9781400033416',
                'description' => 'A novel set in Harlem during the Jazz Age.'
            ],
            [
                'title' => 'Paradise',
                'author' => 'Toni Morrison',
                'year' => 1997,
                'genre' => 'Fiction',
                'isbn' => '9781400033423',
                'description' => 'A novel about an all-black town in Oklahoma.'
            ],
            [
                'title' => 'Love',
                'author' => 'Toni Morrison',
                'year' => 2003,
                'genre' => 'Fiction',
                'isbn' => '9781400033447',
                'description' => 'A novel about the relationships between women in a small town.'
            ],
            [
                'title' => 'A Mercy',
                'author' => 'Toni Morrison',
                'year' => 2008,
                'genre' => 'Fiction',
                'isbn' => '9781400033454',
                'description' => 'A novel set in 17th-century America about slavery and freedom.'
            ],
            [
                'title' => 'Home',
                'author' => 'Toni Morrison',
                'year' => 2012,
                'genre' => 'Fiction',
                'isbn' => '9781400033461',
                'description' => 'A novel about a Korean War veteran returning home.'
            ],
            [
                'title' => 'God Help the Child',
                'author' => 'Toni Morrison',
                'year' => 2015,
                'genre' => 'Fiction',
                'isbn' => '9781400033478',
                'description' => 'A novel about childhood trauma and its lasting effects.'
            ]
        ];

        foreach ($books as $bookData) {
            Book::create($bookData);
        }
    }
}
