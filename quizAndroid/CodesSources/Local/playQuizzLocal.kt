package com.example.cocoquizz.Local

import android.graphics.Color
import android.os.Bundle
import android.view.View
import android.widget.*

import com.example.cocoquizz.Objets.Question
import com.example.cocoquizz.Objets.QuestionReponse
import com.example.cocoquizz.Objets.Reponse
import com.example.cocoquizz.R

import java.util.ArrayList

import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat

class playQuizzLocal : AppCompatActivity() {

    lateinit var dataBase: QuestionsDataBase
    private val jeu = ArrayList<QuestionReponse>()
    private var countNbJeu = 0
    private var score = 0

    /*
     * Get All themes in DB and create an ArrayList
     */
    val themes: ArrayList<String>
        get() {
            val themes = ArrayList<String>()
            val cursor = dataBase.cursorTheme
            cursor.moveToFirst()
            while (!cursor.isAfterLast) {
                themes.add(cursor.getString(1))
                cursor.moveToNext()
            }
            cursor.close()
            return themes
        }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.choose_theme)
        dataBase = QuestionsDataBase(this)

        val themes = themes

        if (themes.size == 0) {
            setContentView(R.layout.no_theme)
            return
        }

        /*
         * Display all themes
         */
        val themeAdapter = ArrayAdapter(
            this,
            R.layout.items,
            R.id.theme_name,
            themes
        )

        val themeList = ListView(this)
        setContentView(themeList)
        themeList.adapter = themeAdapter

        themeList.onItemClickListener =
            AdapterView.OnItemClickListener { adapterView, view, position, rowId ->
                //String message = "You clicked on " + themes.get(position);
                play(themes[position])
            }
    }

    /*
     *
     *
     * PLAY FUNCTIONS
     *
     *
     *
     */

    /*
     * Play one quizz with the theme name
     */
    fun play(theme: String) {
        val theme_id = dataBase.getThemeId(theme)

        /*
        SELECT id_question, texteQuestion, reponse_id FROM QUESTION WHERE id_theme = quizzTheme
         */

        val cursorQ = dataBase.getCursorForQuestion(theme_id)
        val nbQuestion = cursorQ.count

        cursorQ.moveToFirst()

        while (!cursorQ.isAfterLast) {

            val q = Question(cursorQ.getInt(0), cursorQ.getString(1), cursorQ.getInt(2))
            val reps = ArrayList<Reponse>()

            /*
            "SELECT * FROM REPONSE where id_question="+questionNumber
             */
            val cursorR = dataBase.getCursorForProposition(q.id_question)
            cursorR.moveToFirst()

            while (!cursorR.isAfterLast) {
                reps.add(Reponse(cursorR.getInt(0), cursorR.getString(1), cursorR.getInt(2)))
                cursorR.moveToNext()
            }
            val qr = QuestionReponse(q, reps)
            jeu.add(qr)
            cursorQ.moveToNext()
        }

        if (nbQuestion != 0) {
            setContentView(R.layout.play_quizz)
            afficher()
        } else { //The quizz have not question
            super.finish()
            Toast.makeText(this,
            "Ce thème ne comporte pas de question",
            Toast.LENGTH_SHORT
            ).show()

        }
    }

    /*
     *
     * VERIFICATION ANSWERS FUNCTIONS
     *
     *
     */

    fun clicReponse1(v: View) {
        verif(1)
    }

    fun clicReponse2(v: View) {
        verif(2)
    }

    fun clicReponse3(v: View) {
        verif(3)
    }

    fun clicReponse4(v: View) {
        verif(4)
    }

    /*
     * Verification of the answer clicked by the user is correct
     */

    fun verif(reponseProposee: Int) {
        val scoreT = findViewById(R.id.textViewScore) as TextView

        var msg = ""

        if (reponseProposee == jeu.get(countNbJeu - 1).question.reponseOk_id) {
            score += 2
            scoreT.setText("Score : $score")
            msg = "Bonne réponse, Bravo !"
        } else {
            score -= 1
            scoreT.setText("Score : $score")
            msg = "Mauvaise réponse ! "
        }

        Toast.makeText(this, msg, Toast.LENGTH_SHORT).show()

        if (countNbJeu == jeu.size) { //There is no more questions in quizz
            setContentView(R.layout.fin_jeu)
            Toast.makeText(this, msg, Toast.LENGTH_SHORT).show()
            val s = findViewById(R.id.textViewFinScore) as TextView
            s.setText("Votre score : $score")
        } else {
            afficher()
        }
    }

    /*
     * Display one question and his answers on the different fields
     */

    fun afficher() {
        val qstV = findViewById(R.id.textViewQuestion) as TextView
        val b1 = findViewById(R.id.buttonRep1) as Button
        val b2 = findViewById(R.id.buttonRep2) as Button
        val b3 = findViewById(R.id.buttonRep3) as Button
        val b4 = findViewById(R.id.buttonRep4) as Button

        /*
         * There are all the time 4 buttons, but the questions have not eveytime 4 answers. So the button are disabled and color red (same of the background)
         * And if there are an answer n for the button n, this button are enable and his color is set to yellow
         */

        b1.setText("")
        b1.setBackgroundColor(Color.parseColor("#CC0000"))
        b1.setEnabled(false)

        b2.setText("")
        b2.setBackgroundColor(Color.parseColor("#CC0000"))
        b2.setEnabled(false)

        b3.setText("")
        b3.setBackgroundColor(Color.parseColor("#CC0000"))
        b3.setEnabled(false)

        b4.setText("")
        b4.setBackgroundColor(Color.parseColor("#CC0000"))
        b4.setEnabled(false)

        val first = jeu.get(countNbJeu)
        qstV.setText(first.question.texteQuestion)

        var i = 0
        for (r in first.reponses) {
            when (i) {
                0 -> {
                    b1.setText(r.texteReponse)
                    b1.setBackgroundColor(Color.parseColor("#F8EF31"))
                    b1.setEnabled(true)
                }
                1 -> {
                    b2.setText(r.texteReponse)
                    b2.setBackgroundColor(Color.parseColor("#F8EF31"))
                    b2.setEnabled(true)
                }
                2 -> {
                    b3.setText(r.texteReponse)
                    b3.setBackgroundColor(Color.parseColor("#F8EF31"))
                    b3.setEnabled(true)
                }
                3 -> {
                    b4.setText(r.texteReponse)
                    b4.setBackgroundColor(Color.parseColor("#F8EF31"))
                    b4.setEnabled(true)
                }
            }
            i++
        }
        countNbJeu++
    }

    /*
     *
     * ADD SCORE FUNCTIONS
     *
     *
     */

    /*
     * Allow user to edit his name to add the score in the DB
     */
    fun addScore(v: View){
        setContentView(R.layout.add_score_bd)
        var textScore = findViewById(R.id.TextViewScoreRappel) as TextView
        textScore.setText("Score : $score")
    }

    /*
     * Add the score in DB
     */

    fun addScoreMenu(v: View){
        var p = findViewById(R.id.editTextPseudo) as EditText
        dataBase.addScore(score, p.getText().toString().replace("\"", "\'"))
        super.finish()
    }

    /*
     * MENU
     */

    fun menu(v: View) {
        super.finish()
    }

}
