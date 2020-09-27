package com.example.cocoquizz.Gestion

import android.os.Bundle
import android.view.View
import android.widget.*

import com.example.cocoquizz.Local.QuestionsDataBase
import com.example.cocoquizz.R

import java.util.ArrayList

import androidx.appcompat.app.AppCompatActivity

class gestion : AppCompatActivity() {

    lateinit var dataBase: QuestionsDataBase //DB
    lateinit var themeList: ListView //List for the print of all themes in RecyclerView
    lateinit var questionsRV: ListView //List for the print of all questions in RecyclerView

    private var theme_id_modif = 0 //Theme id to modify in DB
    private var question_id_modif = 0 //Question id to modify in DB

    private var the = "" //Theme for add one question in DB

    /*
     * ArrayList of all themes in DB for print in RecyclerView
     */
    val themes: ArrayList<String>
        get() {
            val themes = ArrayList<String>()
            val cursor = dataBase.cursorTheme
            cursor.moveToFirst()
            while (!cursor.isAfterLast()) {
                themes.add(cursor.getString(1))
                cursor.moveToNext()
            }
            cursor.close()
            return themes
        }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.gerer_accueil)
        dataBase = QuestionsDataBase(this)
    }

    /*
     * Print all themes in Recycler View
     */

    fun affiche_themes(themes: ArrayList<String>) {
        if (themes.size == 0) {
            setContentView(R.layout.no_theme)
            return
        }
        setContentView(R.layout.supprimer_quizz)
        val themeAdapter = ArrayAdapter(
            this,
            R.layout.items,
            R.id.theme_name,
            themes
        )

        themeList = ListView(this)
        setContentView(themeList)
        themeList.adapter = themeAdapter
    }

    /*
     *
     *
     *     DELETE A QUIZZ
     *
     *
     */

    /*
     * Delete one theme and all his questions in DB
     */
    fun supprimer(v: View) {
        val themes = themes
        affiche_themes(themes)
        themeList.onItemClickListener =
            AdapterView.OnItemClickListener { adapterView, view, position, rowId ->
                //String message = "You clicked on " + themes.get(position);
                supprimerBD(themes[position])
                setContentView(R.layout.supprimer_quizz_ok)
                val t = findViewById(R.id.textViewSuppOk) as TextView
                t.setText("Le quizz " + themes[position] + " a bien été supprimé")
            }
    }

    /*
     *
     *
     *     EDIT A NAME QUIZZ
     *
     *
     */

    fun modifier(v: View) {
        setContentView(R.layout.modification)
    }

    /*
     * The user choose the theme to modify in Recycler View
     */

    fun modifTheme(v: View) {
        val themes = themes
        affiche_themes(themes)
        themeList.onItemClickListener =
            AdapterView.OnItemClickListener { adapterView, view, position, rowId ->
                setContentView(R.layout.modif_theme)
                val et = findViewById(R.id.editTextTheme) as EditText
                et.setText(themes[position])
                theme_id_modif = dataBase.getThemeId(themes[position])
            }
    }

    /*
     *
     *
     *     EDIT A QUESTION
     *
     *
     */

    /*
     * Display all themes
     */

    fun modifQst(v: View) {

        val themes = themes
        affiche_themes(themes)
        themeList.onItemClickListener =
            AdapterView.OnItemClickListener { adapterView, view, position, rowId ->
                afficherQuestion(themes[position])
            }
    }

    /*
     * Display all questions of one theme
     */

    fun afficherQuestion(theme : String){
        val qst = arrayListOf<String>()

        val cursorQst = dataBase.getAllQuestionTheme(theme)

        cursorQst.moveToFirst()

        while (!cursorQst.isAfterLast){
            qst.add(cursorQst.getString(0))
            cursorQst.moveToNext()
        }

        setContentView(R.layout.questions_recycler_view)
        val themeAdapter = ArrayAdapter<String>(
            this,
            R.layout.items,
            R.id.theme_name,
            qst
        )

        questionsRV = ListView(this)
        setContentView(questionsRV)
        questionsRV.adapter = themeAdapter

        questionsRV.onItemClickListener =
            AdapterView.OnItemClickListener { adapterView, view, position, rowId ->
                modifierQuestion(
                    qst.get(position)
                )
            }
    }

    /*
     * User can edit the question and the answers
     */

    fun modifierQuestion(question: String) {
        setContentView(R.layout.modifier_question)

        val q = dataBase.getOneQuestion(question)

        val etLabel = findViewById(R.id.editTextLabelQST) as EditText
        etLabel.setText(q.texteQuestion)

        question_id_modif = q.id_question

        val cursorR = dataBase.getCursorForProposition(question_id_modif)
        cursorR.moveToFirst()
        val rep = ArrayList<String>()
        while (!cursorR.isAfterLast()) {
            rep.add(cursorR.getString(1))
            cursorR.moveToNext()
        }

        val size = rep.size

        val tv = findViewById(R.id.textViewBonneRep) as TextView
        tv.setText("La bonne réponse est : " + q.reponseOk_id)

        val r1 = findViewById(R.id.editTextR1) as EditText
        if (0 < size)
            r1.setText(rep[0])

        val r2 = findViewById(R.id.editTextR2) as EditText
        if (1 < size)
            r2.setText(rep[1])

        val r3 = findViewById(R.id.editTextR3) as EditText
        if (2 < size)
            r3.setText(rep[2])

        val r4 = findViewById(R.id.editTextR4) as EditText
        if (3 < size)
            r4.setText(rep[3])
    }

    /*
     * Add the question and restart the edition
     */

    fun modif1(v: View) {
        addBD()
        setContentView(R.layout.modification)
        Toast.makeText(this, "La question à bien été modifiée", Toast.LENGTH_SHORT).show()
    }

    /*
     * Add the question and return in the main menu
     */

    fun modif2(v: View) {
        addBD()
        super.finish()
        Toast.makeText(this, "La question à bien été modifiée", Toast.LENGTH_SHORT).show()
    }

    /*
     *
     *
     *     ADD A NEW QUESTION
     *
     *
     */

    /*
     * Display all themes
     */

    fun questionAdd(v: View){
        val themes = themes
        affiche_themes(themes)
        themeList.onItemClickListener =
            AdapterView.OnItemClickListener { adapterView, view, position, rowId ->
                the = themes[position]
                setContentView(R.layout.create_questions_quizz)
            }
    }

    /*
     * User can create one question for the theme chosen
     */

    fun addQuestion(v: View){
        val qst = findViewById(R.id.editTextQuestion) as EditText

        val rep1 = findViewById(R.id.editTextREP1) as EditText
        val rep2 = findViewById(R.id.editTextREP2) as EditText
        val rep3 = findViewById(R.id.editTextREP3) as EditText
        val rep4 = findViewById(R.id.editTextREP4) as EditText

        val reps = ArrayList<String>()
        reps.add(rep1.getText().toString())
        reps.add(rep2.getText().toString())
        reps.add(rep3.getText().toString())
        reps.add(rep4.getText().toString())

        val reponseOK = findViewById(R.id.editTextReponseCorrecte) as EditText
        if (qst.getText().toString().length == 0) {
            Toast.makeText(this, "Vous devez remplir le champ Question", Toast.LENGTH_SHORT)
                .show()
            return
        }

        if (reponseOK.getText().toString().length == 0) {
            Toast.makeText(this, "Vous devez chosir la bonne réponse", Toast.LENGTH_SHORT)
                .show()
            return
        }

        val repOK = Integer.parseInt(reponseOK.getText().toString())

        if (repOK < 0 || repOK > 4) {
            Toast.makeText(
                this,
                "La bonne réponse doit être entre 1 et 4, recommencez",
                Toast.LENGTH_SHORT
            ).show()
            return
        }

        val id_question = addQuestionBD(qst.getText().toString().replace("\"", "\'"), repOK)

        for (s in reps) {
            if (s.length > 0) {
                addReponsesBD(s, id_question)
            }
        }

        setContentView(R.layout.validation_question_add)
    }

    /*
     *
     *
     * FUNCTIONS RETURN TO MENU
     *
     *
     */

    fun returnMenu(v: View){
        super.finish()
    }

    fun menu(v: View) {
        super.finish()
    }

    /*
     *
     *
     * FUNCTIONS DATABASE HANDLER
     *
     *
     */

    /*
     * Delete one theme
     */
    fun supprimerBD(theme: String) {
        dataBase.supprimerTheme(theme) //The question are delete too
    }

    /*
     * Add one question in DB (with the theme chosen before)
     */

    fun addQuestionBD(label: String, reponseOk_id: Int): Int {
        val question_id = dataBase.getNextId("QUESTION")

        val theme_id = dataBase.getThemeId(the)

        dataBase.creerQuestion(question_id, label, theme_id, reponseOk_id)
        return question_id
    }

    /*
     * Add the answers for the question
     */

    fun addReponsesBD(label: String, idQuestion: Int) {
        val id_reponse = dataBase.getNextId("REPONSE")
        dataBase.creerReponse(id_reponse, label, idQuestion)
    }

    /*
     * Modification of the name of theme
     */

    fun modifBDTh(v: View) {
        val et = findViewById(R.id.editTextTheme) as EditText
        if (et.getText().toString().length == 0) {
            Toast.makeText(this, "Vous devez remplir le champ Thème", Toast.LENGTH_SHORT).show()
            return
        }
        dataBase.modifierTheme(et.getText().toString(), theme_id_modif)
        setContentView(R.layout.modification)
        Toast.makeText(this, "Le thème à bien été modifié", Toast.LENGTH_SHORT).show()
    }

    /*
     * Add The question and all her answers in DB
     */

    fun addBD() {
        val etLabel = findViewById(R.id.editTextLabelQST) as EditText
        if (etLabel.getText().toString().length == 0) {
            Toast.makeText(this, "Vous devez remplir le champ Question", Toast.LENGTH_SHORT).show()
            return
        }
        dataBase.modifierQuestion(question_id_modif, etLabel.getText().toString())

        val cursor = dataBase.getCursorForProposition(question_id_modif)
        cursor.moveToFirst()
        val rep_id = ArrayList<Int>()
        while (!cursor.isAfterLast()) {
            rep_id.add(cursor.getInt(0))
            cursor.moveToNext()
        }

        val size = rep_id.size
        val r1 = findViewById(R.id.editTextR1) as EditText
        if (r1.getText().toString().length != 0) {
            if (0 < size) {
                dataBase.modifierReponse(rep_id[0], r1.getText().toString())
            } else {
                dataBase.creerReponse(
                    dataBase.getNextId("REPONSE"),
                    r1.getText().toString(),
                    question_id_modif
                )
            }
        }

        val r2 = findViewById(R.id.editTextR2) as EditText
        if (r2.getText().toString().length != 0) {
            if (1 < size) {
                dataBase.modifierReponse(rep_id[1], r2.getText().toString())
            } else {
                dataBase.creerReponse(
                    dataBase.getNextId("REPONSE"),
                    r2.getText().toString(),
                    question_id_modif
                )
            }
        }

        val r3 = findViewById(R.id.editTextR3) as EditText
        if (r3.getText().toString().length != 0) {
            if (2 < size) {
                dataBase.modifierReponse(rep_id[2], r3.getText().toString())
            } else {
                dataBase.creerReponse(
                    dataBase.getNextId("REPONSE"),
                    r3.getText().toString(),
                    question_id_modif
                )
            }
        }

        val r4 = findViewById(R.id.editTextR4) as EditText
        if (r4.getText().toString().length != 0) {
            if (3 < size) {
                dataBase.modifierReponse(rep_id[3], r4.getText().toString())
            } else {
                dataBase.creerReponse(
                    dataBase.getNextId("REPONSE"),
                    r4.getText().toString(),
                    question_id_modif
                )
            }
        }
    }
}
